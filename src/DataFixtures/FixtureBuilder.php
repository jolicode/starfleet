<?php

/*
 * This file is part of the Starfleet Project.
 *
 * (c) Starfleet <msantostefano@jolicode.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */

namespace App\DataFixtures;

use App\Entity\Conference;
use App\Entity\Participation;
use App\Entity\Submit;
use App\Entity\Talk;
use App\Entity\User;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class FixtureBuilder
{
    private static ?Generator $faker = null;

    public static function createTalk(array $description = []): Talk
    {
        $description = array_replace([
            'id' => uuid_create(),
            'title' => self::getFaker()->title,
            'intro' => self::getFaker()->text(30),
            'submits' => [],
        ], $description);

        $talk = new Talk();

        ReflectionHelper::setProperty($talk, 'id', $description['id']);
        $talk->setTitle($description['title']);
        $talk->setIntro($description['intro']);

        foreach ($description['submits'] as $submit) {
            $talk->addSubmit($submit);
        }

        return $talk;
    }

    public static function createSubmit(array $description = []): Submit
    {
        $description = array_replace([
            'id' => uuid_create(),
            'submittedAt' => self::getFaker()->dateTime,
            'status' => array_rand(Submit::STATUSES),
            'users' => [],
            'conference' => self::createConference(),
            'talk' => self::createTalk(),
        ], $description);

        $submit = new Submit();

        ReflectionHelper::setProperty($submit, 'id', $description['id']);
        $submit->setSubmittedAt($description['submittedAt']);
        $submit->setStatus($description['status']);
        $submit->setConference($description['conference']);
        $submit->setTalk($description['talk']);

        foreach ($description['users'] as $user) {
            $submit->addUser($user);
        }

        return $submit;
    }

    public static function createUser(array $description = [], ?UserPasswordEncoderInterface $passwordEncoder = null): User
    {
        $description = array_replace([
            'id' => uuid_create(),
            'name' => self::getFaker()->name,
            'submits' => [],
            'participations' => [],
            'roles' => [array_rand(['ROLE_USER', 'ROLE_ADMIN'])],
            'password' => self::getFaker()->password,
            'email' => self::getFaker()->email,
        ], $description);

        $user = new User();

        ReflectionHelper::setProperty($user, 'id', $description['id']);
        $user->setName($description['name']);
        $user->setRoles($description['roles']);
        $user->setEmail($description['email']);
        if ($passwordEncoder) {
            $user->setPassword($passwordEncoder->encodePassword($user, $description['password']));
        } else {
            $user->setPassword($description['password']);
        }

        foreach ($description['submits'] as $submit) {
            $user->addSubmit($submit);
        }

        foreach ($description['participations'] as $participation) {
            $user->addParticipation($participation);
        }

        return $user;
    }

    public static function createConference(array $description = []): Conference
    {
        $description = array_replace([
            'id' => uuid_create(),
            'name' => self::getFaker()->name,
            'location' => self::getFaker()->country,
            'siteUrl' => self::getFaker()->url,
            'cfpUrl' => self::getFaker()->url,
            'startAt' => $date = self::getFaker()->dateTimeThisMonth->modify('+ 10 days'),
            'endAt' => clone $date->modify('+ 1 days'),
            'cfpEndAt' => clone $date->modify('- 7 days'),
            'participations' => [],
        ], $description);

        $conference = new Conference();

        ReflectionHelper::setProperty($conference, 'id', $description['id']);
        $conference->setName($description['name']);
        $conference->setLocation($description['location']);
        $conference->setSiteUrl($description['siteUrl']);
        $conference->setCfpUrl($description['cfpUrl']);
        $conference->setStartAt($description['startAt']);
        $conference->setEndAt($description['endAt']);

        foreach ($description['participations'] as $participation) {
            $conference->addParticipation($participation);
        }

        return $conference;
    }

    public static function createParticipation(array $description = []): Participation
    {
        $description = array_replace([
            'id' => uuid_create(),
            'conference' => self::createConference(),
            'participant' => self::createUser(),
            'asSpeaker' => self::getFaker()->boolean,
            'marking' => ['validated' => random_int(0, 1)],
        ], $description);

        $participation = new Participation();

        ReflectionHelper::setProperty($participation, 'id', $description['id']);
        $participation->setConference($description['conference']);
        $participation->setParticipant($description['participant']);
        $participation->setAsSpeaker($description['asSpeaker']);
        $participation->setMarking($description['marking']);

        return $participation;
    }

    private static function getFaker(): \Faker\Generator
    {
        if (null === self::$faker) {
            self::$faker = \Faker\Factory::create();
        }

        return self::$faker;
    }
}
