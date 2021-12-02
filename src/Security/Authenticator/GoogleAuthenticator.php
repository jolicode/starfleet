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
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function supports(Request $request): bool
    {
        return 'connect_google_check' === $request->attributes->get('_route');
    }

    public function authenticate(Request $request): PassportInterface
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken, function () use ($accessToken, $client) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $existingUser = $this->em->getRepository(User::class)
                    ->findOneBy(['googleId' => $googleUser->getId()])
                ;

                if ($existingUser) {
                    return $existingUser;
                }

                /** @var null|User $user */
                $user = $this->em->getRepository(User::class)
                    ->findOneBy(['email' => $googleUser->getEmail()])
                ;

                if ($user instanceof User) {
                    $user->setGoogleId($googleUser->getId());

                    $this->em->flush();

                    return $user;
                }

                $explodedEmail = explode('@', $googleUser->getEmail());
                $domain = array_pop($explodedEmail);
                $allowedDomains = explode(',', $_SERVER['APP_ALLOWED_EMAIL_DOMAINS'] ?: '');

                if (!\in_array($domain, $allowedDomains)) {
                    throw new AuthenticationException('You are not allowed to create an account.');
                }

                $user = new User();
                $user->setName($googleUser->getFirstName() . ' ' . $googleUser->getLastName());
                $user->setEmail($googleUser->getEmail());
                $user->setGoogleId($googleUser->getId());

                $this->em->persist($user);
                $this->em->flush();

                return $user;
            })
        );
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('connect_google'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): Response
    {
        $url = $this->getPreviousUrl($request, $providerKey);

        return new RedirectResponse($url ?: $this->urlGenerator->generate('user_account'));
    }
}
