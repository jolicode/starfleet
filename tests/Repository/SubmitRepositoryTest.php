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

use App\Entity\Submit;
use App\Repository\SubmitRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubmitRepositoryTest extends KernelTestCase
{
    public function testUpdateDoneSubmitsWork()
    {
        static::bootKernel();

        $submitRepository = static::$container->get(SubmitRepository::class);
        $submitRepository->updateDoneSubmits();

        $today = new \DateTime();
        $today->setTime(0, 0);

        $pastAcceptedSubmitsCount = $submitRepository->createQueryBuilder('s')
            ->select('count(s)')
            ->innerJoin('s.conference', 'c')
            ->andWhere('s.status = :status_accepted')
            ->andWhere('c.endAt < :today')
            ->setParameters([
                'status_accepted' => Submit::STATUS_ACCEPTED,
                'today' => $today,
            ])
            ->getQuery()
            ->getSingleScalarResult()
        ;

        self::assertSame(0, $pastAcceptedSubmitsCount);
    }
}
