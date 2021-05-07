<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\Tests\Security\Voter;

use App\Entity\Participation;
use App\Entity\User;
use App\Security\Voter\ParticipationVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class ParticipationVoterTest extends TestCase
{
    private ParticipationVoter $participationVoter;

    protected function setUp()
    {
        $this->participationVoter = new ParticipationVoter(new RoleHierarchy([]));
    }

    public function testAbstains()
    {
        $token = $this->prophesize(TokenInterface::class);
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $this->participationVoter->vote($token->reveal(), new Participation(), ['SOMETHING_ELSE']));
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $this->participationVoter->vote($token->reveal(), null, ['ROLE_PARTICIPATION_SHOW']));
    }

    public function testDeniesIfNotLogged()
    {
        $token = $this->prophesize(TokenInterface::class);

        $user = new User();
        $user->setName('Coco');
        $participation = new Participation();
        $participation->setParticipant($user);

        self::assertSame(VoterInterface::ACCESS_DENIED, $this->participationVoter->vote($token->reveal(), $participation, ['ROLE_PARTICIPATION_SHOW']));
    }

    /**
     * @dataProvider provideVoteTests
     * */
    public function testSupports(int $expected, User $user, Participation $participation): void
    {
        $token = new UsernamePasswordToken($user, 'password', 'provider_key');

        self::assertSame($expected, $this->participationVoter->vote($token, $participation, ['ROLE_PARTICIPATION_SHOW']));
    }

    public function provideVoteTests()
    {
        $coco = new User();
        $coco->setName('Coco');
        $jojo = new User();
        $jojo->setName('Jojo');

        $cocoParticipation = new Participation();
        $cocoParticipation->setParticipant($coco);
        $jojoParticipation = new Participation();
        $jojoParticipation->setParticipant($jojo);

        yield 'Jojo can see his own participation' => [VoterInterface::ACCESS_GRANTED, $jojo, $jojoParticipation];
        yield 'Jojo can`t see other people\'s participations' => [VoterInterface::ACCESS_DENIED, $jojo, $cocoParticipation];
    }
}
