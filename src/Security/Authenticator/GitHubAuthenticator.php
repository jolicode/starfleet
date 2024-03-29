<?php

namespace App\Security\Authenticator;

use App\Entity\User;
use App\Repository\UserRepository;
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
        private UserRepository $userRepository,
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

                $response = $this->httpClient->request('GET', sprintf('https://api.github.com/users/%s/orgs', $githubUser->getNickname()));
                $githubOrganizations = json_decode($response->getContent());

                $isUserInAllowedOrganization = \count(array_filter($githubOrganizations, function ($organization) {
                    return $organization->login === $this->allowedGitHubOrganization;
                })) > 0;

                if (!$isUserInAllowedOrganization) {
                    return null;
                }

                $existingUser = $this->userRepository->findOneBy(['githubId' => $githubUser->getId()]) ?: $this->userRepository->findOneBy(['name' => $githubUser->getName()]);

                if ($existingUser) {
                    if (!$existingUser->getGithubId()) {
                        $existingUser->setGithubId($githubUser->getId());
                        $this->em->flush();
                    }

                    return $existingUser;
                }

                $response = $this->httpClient->request('GET', 'https://api.github.com/user/emails', [
                    'headers' => [
                        'Authorization' => sprintf('token %s', $accessToken->getToken()),
                    ],
                ]);
                $userEmails = json_decode($response->getContent());

                foreach ($userEmails as $email) {
                    $user = $this->userRepository->findOneBy(['email' => $email->email]);

                    if ($user) {
                        $user->setGithubId($githubUser->getId());

                        $this->em->flush();

                        return $user;
                    }

                    if ($email->primary && $email->verified) {
                        $user = new User();
                        $user->setName($githubUser->getName());
                        $user->setEmail($email->email);
                        $user->setGithubId($githubUser->getId());

                        $this->em->persist($user);
                        $this->em->flush();

                        return $user;
                    }
                }

                return null;
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
        $request->getSession()->getFlashBag()->add('error', 'Authentication failed. This may be because your GitHub user is not in the allowed organization, the membership is private, or we couldn\'t reach a primary and verified email for your account.');

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        $url = $this->getPreviousUrl($request, $providerKey);

        return new RedirectResponse($url ?: $this->urlGenerator->generate('user_account'));
    }
}
