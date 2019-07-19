<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Functional;

use App\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait CreateAuthenticatedClientTrait
{
    protected static function createAuthenticatedClient(User $user = null)
    {
        $client = static::createClient();
        $container = static::$kernel->getContainer();

        if (!$user) {
            $user = $container->get('doctrine')->getRepository(User::class)
                ->findOneByEmail('admin@starfleet.app');
        }

        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());

        $session = $container->get('session');
        $session->set('_security_main', serialize($token));
        $session->save();

        $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return $client;
    }
}
