<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Repository;

use App\Repository\SubmitRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubmitRepositoryTest extends KernelTestCase
{
    public function testUpdateDoneSubmitsWork()
    {
        static::bootKernel();

        $submitRepository = static::$container->get(SubmitRepository::class);
        $submitRepository->updateDoneSubmits();

        self::assertSame(0, \count($submitRepository->getDoneSubmits()));
    }
}
