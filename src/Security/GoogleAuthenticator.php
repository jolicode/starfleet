<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Security;

use App\Entity\User;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Client\OAuth2Client;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleAuthenticator extends SocialAuthenticator
{
    private $clientRegistry;
    private $em;
    private $urlGenerator;

    public function __construct(ClientRegistry $clientRegistry, RegistryInterface $doctrine, UrlGeneratorInterface $urlGenerator)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $doctrine->getManager();
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request)
    {
        return 'connect_google_check' === $request->attributes->get('_route');
    }

    public function getCredentials(Request $request)
    {
        return $this->fetchAccessToken($this->getGoogleClient());
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var GoogleUser $googleUser */
        $googleUser = $this->getGoogleClient()
            ->fetchUserFromToken($credentials);

        $existingUser = $this->em->getRepository(User::class)
            ->findOneBy(['googleId' => $googleUser->getId()]);

        if ($existingUser) {
            return $existingUser;
        }

        $user = $this->em->getRepository(User::class)
            ->findOneBy(['email' => $googleUser->getEmail()]);

        if ($user instanceof User) {
            $user->setGoogleId($googleUser->getId());

            $this->em->flush();

            return $user;
        }

        $explodedEmail = explode('@', $googleUser->getEmail());
        $domain = array_pop($explodedEmail);
        $allowedDomains = explode(',', getenv('APP_ALLOWED_EMAIL_DOMAINS') ?: '');

        if (!\in_array($domain, $allowedDomains)) {
            throw new AuthenticationException('You are not allowed to create an account.');
        }

        $user = new User();
        $user->setName($googleUser->getFirstName().' '.$googleUser->getLastName());
        $user->setEmail($googleUser->getEmail());
        $user->setGoogleId($googleUser->getId());

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    private function getGoogleClient(): OAuth2Client
    {
        return $this->clientRegistry->getClient('google');
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->urlGenerator->generate('connect_google'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new RedirectResponse($this->urlGenerator->generate('conferences_list'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $url = $this->getPreviousUrl($request, $providerKey);

        return new RedirectResponse($url ?: $this->urlGenerator->generate('easyadmin'));
    }
}
