<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mailer\Message\CommitteeNewFollowerMessage;

/**
 * @group message
 */
class CommitteeNewFollowerMessageTest extends MessageTestCase
{
    /**
     * @var Committee|null
     */
    private $committee;

    /**
     * @var Adherent|null
     */
    private $newFollower;

    public function testCreate(): void
    {
        $message = CommitteeNewFollowerMessage::create(
            $this->committee,
            [
                $this->createAdherent('jean@example.com', 'Jean', 'Doe'),
                $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            ],
            $this->newFollower,
            'https://enmarche.code/comites/foo-bar/administration'
        );

        self::assertMessage(
            CommitteeNewFollowerMessage::class,
            [
                'committee_name' => 'Comité #1',
                'committee_admin_url' => 'https://enmarche.code/comites/foo-bar/administration',
                'member_first_name' => 'Follower',
                'member_last_name' => 'J.',
                'member_age' => 29,
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
                'committee_name' => 'Comité #1',
                'committee_admin_url' => 'https://enmarche.code/comites/foo-bar/administration',
                'member_first_name' => 'Follower',
                'member_last_name' => 'J.',
                'member_age' => 29,
                'first_name' => 'Jean',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'committee_name' => 'Comité #1',
                'committee_admin_url' => 'https://enmarche.code/comites/foo-bar/administration',
                'member_first_name' => 'Follower',
                'member_last_name' => 'J.',
                'member_age' => 29,
                'first_name' => 'Bernard',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->committee = $this->createMock(Committee::class);

        $this->committee
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Comité #1')
        ;

        $this->newFollower = $this->createAdherent('follower@example.com', 'Follower', 'Jones');

        $this->newFollower
            ->expects(self::once())
            ->method('getAge')
            ->willReturn(29)
        ;
    }

    protected function tearDown()
    {
        $this->committee = null;
        $this->newFollower = null;
    }
}
