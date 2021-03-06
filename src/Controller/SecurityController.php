<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token_intention' => 'authenticate',
            'target_path' => $this->generateUrl('easyadmin'),
            'sign_in_label' => 'Login',
        ]);
    }

    #[Route(path: '/connect/google', name: 'connect_google')]
    public function connectGoogleAction(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry->getClient('google')->redirect([], []);
    }

    #[Route(path: '/connect/google/check', name: 'connect_google_check')]
    public function connectGoogleCheckAction(Request $request, ClientRegistry $clientRegistry): Response
    {
        $client = $clientRegistry->getClient('google');

        return $this->redirectToRoute('conferences_list');
    }

    #[Route(path: '/connect/github', name: 'connect_github')]
    public function connectGitHubAction(ClientRegistry $clientRegistry): Response
    {
        return $clientRegistry->getClient('github')->redirect([], []);
    }

    #[Route(path: '/connect/github/check', name: 'connect_github_check')]
    public function connectGitHubCheckAction(Request $request): void
    {
    }

    #[Route(path: '/logout', name: 'logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \Exception('Don\'t forget to activate logout in security.yaml');
    }
}
