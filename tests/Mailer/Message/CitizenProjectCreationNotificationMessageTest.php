<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\Message\CitizenProjectCreationNotificationMessage;

/**
 * @group message
 */
class CitizenProjectCreationNotificationMessageTest extends MessageTestCase
{
    /**
     * @var CitizenProject|null
     */
    private $citizenProject;

    public function testCreate(): void
    {
        $message = CitizenProjectCreationNotificationMessage::create(
            $this->createAdherent('bernard@example.com', 'Bernard', 'Smith'),
            $this->citizenProject,
            $this->createAdherent('creator@example.com', 'Créateur', 'Jones')
        );

        self::assertMessage(
            CitizenProjectCreationNotificationMessage::class,
            [
                'first_name' => 'Bernard',
                'citizen_project_name' => 'Projet Citoyen #1',
                'citizen_project_slug' => 'projet-citoyen-1',
                'creator_first_name' => 'Créateur',
                'creator_last_name' => 'Jones',
            ],
            $message
        );

        self::assertSender(null, 'projetscitoyens@en-marche.fr', $message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'first_name' => 'Bernard',
                'citizen_project_name' => 'Projet Citoyen #1',
                'citizen_project_slug' => 'projet-citoyen-1',
                'creator_first_name' => 'Créateur',
                'creator_last_name' => 'Jones',
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
        $this->citizenProject
            ->expects(self::once())
            ->method('getSlug')
            ->willReturn('projet-citoyen-1')
        ;
    }

    protected function tearDown()
    {
        $this->citizenProject = null;
    }
}
