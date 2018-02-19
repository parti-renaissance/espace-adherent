<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\Message\CitizenProjectNewFollowerMessage;

/**
 * @group message
 */
class CitizenProjectNewFollowerMessageTest extends MessageTestCase
{
    /**
     * @var CitizenProject|null
     */
    private $citizenProject;

    /**
     * @var Adherent|null
     */
    private $newFollower;

    public function testCreate(): void
    {
        $message = CitizenProjectNewFollowerMessage::create(
            $this->citizenProject,
            [
                $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
                $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            ],
            $this->newFollower
        );

        self::assertMessage(
            CitizenProjectNewFollowerMessage::class,
            [
                'citizen_project_name' => 'Projet Citoyen #1',
                'follower_first_name' => 'Follower',
                'follower_last_name' => 'J.',
                'follower_age' => 29,
                'follower_city' => 'Lille',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertReplyTo('follower@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'citizen_project_name' => 'Projet Citoyen #1',
                'follower_first_name' => 'Follower',
                'follower_last_name' => 'J.',
                'follower_age' => 29,
                'follower_city' => 'Lille',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'citizen_project_name' => 'Projet Citoyen #1',
                'follower_first_name' => 'Follower',
                'follower_last_name' => 'J.',
                'follower_age' => 29,
                'follower_city' => 'Lille',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->citizenProject = $this->createMock(CitizenProject::class);

        $this->citizenProject
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Projet Citoyen #1')
        ;

        $this->newFollower = $this->createAdherent('follower@example.com', 'Follower', 'Jones');

        $this->newFollower
            ->expects(self::once())
            ->method('getCityName')
            ->willReturn('Lille')
        ;
        $this->newFollower
            ->expects(self::once())
            ->method('getAge')
            ->willReturn(29)
        ;
    }

    protected function tearDown()
    {
        $this->citizenProject = null;
        $this->newFollower = null;
    }
}
