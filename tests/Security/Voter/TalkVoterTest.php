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

use App\Entity\Submit;
use App\Entity\Talk;
use App\Entity\User;
use App\Security\Voter\TalkVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class TalkVoterTest extends TestCase
{
    private TalkVoter $talkVoter;

    protected function setUp()
    {
        $this->talkVoter = new TalkVoter(new RoleHierarchy([]));
    }

    public function testAbstains()
    {
        $token = $this->prophesize(TokenInterface::class);
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $this->talkVoter->vote($token->reveal(), new Talk(), ['SOMETHING_ELSE']));
        self::assertSame(VoterInterface::ACCESS_ABSTAIN, $this->talkVoter->vote($token->reveal(), null, ['ROLE_TALK_SHOW']));
    }

    public function testDeniesIfNotLogged()
    {
        $token = $this->prophesize(TokenInterface::class);

        $talk = new Talk();
        $submit = new Submit();
        $user = new User();
        $user->setName('Coco');
        $submit->addUser($user);
        $talk->addSubmit($submit);

        self::assertSame(VoterInterface::ACCESS_DENIED, $this->talkVoter->vote($token->reveal(), $talk, ['ROLE_TALK_SHOW']));
    }

    /**
     * @dataProvider provideVoteTests
     * */
    public function testSupports(int $expected, User $user, Talk $talk): void
    {
        $token = new UsernamePasswordToken($user, 'password', 'provider_key');

        self::assertSame($expected, $this->talkVoter->vote($token, $talk, ['ROLE_TALK_SHOW']));
    }

    public function provideVoteTests()
    {
        $coco = new User();
        $coco->setName('Coco');
        $jojo = new User();
        $jojo->setName('Jojo');

        $phpSubmit = new Submit();
        $phpSubmit->addUser($coco);
        $jsSubmit = new Submit();
        $jsSubmit->addUser($jojo);

        $phpTalk = new Talk();
        $phpTalk->addSubmit($phpSubmit);
        $jsTalk = new Talk();
        $jsTalk->addSubmit($jsSubmit);

        yield 'Coco can see his own talk' => [VoterInterface::ACCESS_GRANTED, $coco, $phpTalk];
        yield 'Coco can`t see other people\'s talks' => [VoterInterface::ACCESS_DENIED, $coco, $jsTalk];
    }
}
