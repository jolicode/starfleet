<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Controller\UserAccount;

use App\Form\UserAccount\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    #[Route(path: '/user/profile', name: 'user_profile')]
    public function userProfile(Request $request): Response
    {
        $form = $this->createForm(UserProfileType::class, $this->getUser());

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Profile successfully updated');
        }

        return $this->render('user/profile/user_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
