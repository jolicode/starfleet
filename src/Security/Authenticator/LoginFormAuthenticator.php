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

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): RedirectResponse
    {
        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    public function supports(Request $request): ?bool
    {
        return 'login' === $request->attributes->get('_route')
           && $request->isMethod('POST')
        ;
    }

    public function authenticate(Request $request): PassportInterface
    {
        $username = $request->request->get('_username');
        $password = $request->request->get('_password');

        if (null === $username) {
            throw new CustomUserMessageAuthenticationException('No username provided.');
        }

        if (null === $password) {
            throw new CustomUserMessageAuthenticationException('No password provided.');
        }

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($password),
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('user_account'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // @phpstan-ignore-next-line
        $request->getSession()->getFlashBag()->add('error', 'Either your email or password is invalid.');

        return new RedirectResponse($this->urlGenerator->generate('login'));
    }

    protected function getLoginUrl(): string
    {
        return $this->urlGenerator->generate('login');
    }
}
