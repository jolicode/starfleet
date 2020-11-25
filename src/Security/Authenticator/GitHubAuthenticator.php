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
use Doctrine\Persistence\ManagerRegistry;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GitHubAuthenticator extends SocialAuthenticator
{
    private $clientRegistry;
    private $em;
    private $urlGenerator;
    private $allowedGitHubOrganization;
    private $httpClient;
    private $requestStack;

    public function __construct(ClientRegistry $clientRegistry, ManagerRegistry $registry,
        UrlGeneratorInterface $urlGenerator, RequestStack $requestStack, string $allowedGitHubOrganization)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $registry->getManager();
        $this->urlGenerator = $urlGenerator;
        $this->allowedGitHubOrganization = $allowedGitHubOrganization;
        $this->httpClient = HttpClient::create();
        $this->requestStack = $requestStack;
    }

    public function supports(Request $request): bool
    {
        return 'connect_github_check' === $request->attributes->get('_route');
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGitHubClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?User
    {
        /** @var GithubResourceOwner $githubUser */
        $githubUser = $this->getGitHubClient()
            ->fetchUserFromToken($credentials);

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

        /** @var User $user */
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
    }

    private function getGitHubClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('github');
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->urlGenerator->generate('connect_github'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->requestStack->getCurrentRequest()->getSession()->getFlashBag()->add('error', 'Your GitHub user is not in the allowed organization, or the membership is private.');

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $url = $this->getPreviousUrl($request, $providerKey);

        return new RedirectResponse($url ?: $this->urlGenerator->generate('easyadmin'));
    }
}