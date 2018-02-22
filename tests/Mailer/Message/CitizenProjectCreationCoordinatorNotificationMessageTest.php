<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\Message\CitizenProjectCreationCoordinatorNotificationMessage;

/**
 * @group message
 */
class CitizenProjectCreationCoordinatorNotificationMessageTest extends MessageTestCase
{
    /**
     * @var CitizenProject|null
     */
    private $citizenProject;

    public function testCreate(): void
    {
        $message = CitizenProjectCreationCoordinatorNotificationMessage::create(
            $this->createAdherent('coordinator@example.com', 'Coordinateur', 'Smith'),
            $this->citizenProject,
            $this->createAdherent('creator@example.com', 'Créateur', 'Jones'),
            'https://enmarche.code/espace-coordinateur'
        );

        self::assertMessage(
            CitizenProjectCreationCoordinatorNotificationMessage::class,
            [
                'first_name' => 'Coordinateur',
                'citizen_project_name' => 'Projet Citoyen #1',
                'host_first_name' => 'Créateur',
                'host_last_name' => 'Jones',
                'coordinator_space_url' => 'https://enmarche.code/espace-coordinateur',
            ],
            $message
        );

        self::assertSender(null, 'projetscitoyens@en-marche.fr', $message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'coordinator@example.com',
            'Coordinateur Smith',
            [
                'first_name' => 'Coordinateur',
                'citizen_project_name' => 'Projet Citoyen #1',
                'host_first_name' => 'Créateur',
                'host_last_name' => 'Jones',
                'coordinator_space_url' => 'https://enmarche.code/espace-coordinateur',
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
    }

    protected function tearDown()
    {
        $this->citizenProject = null;
    }
}
