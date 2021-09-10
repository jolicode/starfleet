<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Security\Authenticator;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GitHubAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $urlGenerator,
        private HttpClientInterface $httpClient,
        private string $allowedGitHubOrganization,
    ) {
    }

    public function supports(Request $request): bool
    {
        return 'connect_github_check' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): PassportInterface
    {
        $client = $this->clientRegistry->getClient('github');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken, function () use ($accessToken, $client) {
                /** @var GithubResourceOwner $githubUser */
                $githubUser = $client->fetchUserFromToken($accessToken);

                $existingUser = $this->em->getRepository(User::class)
                    ->findOneBy(['githubId' => $githubUser->getId()]);

                $response = $this->httpClient->request('GET', sprintf('https://api.github.com/users/%s/orgs', $githubUser->getNickname()));
                $githubOrganizations = json_decode($response->getContent());

                $isUserInAllowedOrganization = \count(array_filter($githubOrganizations, function ($organization) {
                    return $organization->login === $this->allowedGitHubOrganization;
                })) > 0;

                if (!$isUserInAllowedOrganization) {
                    return null;
                }

                if ($existingUser) {
                    return $existingUser;
                }

                /** @var User|null $user */
                $user = $this->em->getRepository(User::class)
                    ->findOneBy(['email' => $githubUser->getEmail()]);

                if ($user) {
                    $user->setGithubId($githubUser->getId());

                    $this->em->flush();

                    return $user;
                }

                $user = new User();
                $user->setName($githubUser->getName());
                $user->setEmail($githubUser->getEmail());
                $user->setGithubId($githubUser->getId());

                $this->em->persist($user);
                $this->em->flush();

                return $user;
            })
        );
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('connect_github'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // @phpstan-ignore-next-line
        $request->getSession()->getFlashBag()->add('error', 'Your GitHub user is not in the allowed organization, or the membership is private.');

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        $url = $this->getPreviousUrl($request, $providerKey);

        return new RedirectResponse($url ?: $this->urlGenerator->generate('easyadmin'));
    }
}
