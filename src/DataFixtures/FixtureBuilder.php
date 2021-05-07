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

    /** @param array<string,mixed> $description */
    public static function createTalk(array $description = []): Talk
    {
        $description = array_replace([
            'title' => self::getFaker()->title,
            'intro' => self::getFaker()->text(30),
            'submits' => [],
        ], $description);

        $talk = new Talk();

        $talk->setTitle($description['title']);
        $talk->setIntro($description['intro']);

        foreach ($description['submits'] as $submit) {
            $talk->addSubmit($submit);
        }

        return $talk;
    }

    /** @param array<string,mixed> $description */
    public static function createSubmit(array $description = []): Submit
    {
        $description = array_replace([
            'submittedAt' => self::getFaker()->dateTime,
            'status' => array_rand(Submit::STATUSES),
            'users' => [],
            'conference' => self::createConference(),
            'talk' => self::createTalk(),
        ], $description);

        $submit = new Submit();

        $submit->setSubmittedAt($description['submittedAt']);
        $submit->setStatus($description['status']);
        $submit->setConference($description['conference']);
        $submit->setTalk($description['talk']);

        foreach ($description['users'] as $user) {
            $submit->addUser($user);
        }

        return $submit;
    }

    /** @param array<string,mixed> $description */
    public static function createUser(array $description = [], ?UserPasswordEncoderInterface $passwordEncoder = null): User
    {
        $description = array_replace([
            'name' => self::getFaker()->name,
            'submits' => [],
            'participations' => [],
            'roles' => [array_rand(['ROLE_USER', 'ROLE_ADMIN'])],
            'password' => self::getFaker()->password,
            'email' => self::getFaker()->email,
        ], $description);

        $user = new User();

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

    /** @param array<string,mixed> $description */
    public static function createConference(array $description = []): Conference
    {
        $description = array_replace([
            'name' => $cityName = self::getFaker()->city,
            'country' => array_rand(array_flip(['gb', 'es', 'it', 'fr', 'de'])),
            'city' => $cityName,
            'siteUrl' => self::getFaker()->url,
            'cfpUrl' => self::getFaker()->url,
            'startAt' => $date = self::getFaker()->dateTimeThisMonth->modify('+ 10 days'),
            'endAt' => clone $date->modify('+ 1 days'),
            'cfpEndAt' => clone $date->modify('- 7 days'),
            'participations' => [],
            'online' => false,
            'coordinates' => [self::getFaker()->longitude, self::getFaker()->latitude],
            'tags' => [
                'PHP',
                'DevOps',
                'Security',
            ],
        ], $description);

        $conference = new Conference();

        $conference->setName($description['name']);
        $conference->setSiteUrl($description['siteUrl']);
        $conference->setCfpUrl($description['cfpUrl']);
        $conference->setStartAt($description['startAt']);
        $conference->setEndAt($description['endAt']);
        $conference->setCfpEndAt($description['cfpEndAt']);
        $conference->setOnline($description['online']);

        if ($description['online']) {
            $conference->setCity('Online');
        } else {
            $conference->setCity($description['city']);
            $conference->setCountry($description['country']);
            $conference->setCoordinates($description['coordinates']);
        }

        foreach ($description['tags'] as $tag) {
            $conference->addTag($tag);
        }

        foreach ($description['participations'] as $participation) {
            $conference->addParticipation($participation);
        }

        return $conference;
    }

    /** @param array<string,mixed> $description */
    public static function createParticipation(array $description = []): Participation
    {
        $description = array_replace([
            'conference' => self::createConference(),
            'participant' => self::createUser(),
            'asSpeaker' => self::getFaker()->boolean,
            'marking' => ['accepted' => random_int(0, 1)],
        ], $description);

        $participation = new Participation();

        $participation->setConference($description['conference']);
        $participation->setParticipant($description['participant']);
        $participation->setAsSpeaker($description['asSpeaker']);
        $participation->setMarking($description['marking']);

        return $participation;
    }

    private static function getFaker(): Generator
    {
        if (null === self::$faker) {
            self::$faker = \Faker\Factory::create();
        }

        return self::$faker;
    }
}
