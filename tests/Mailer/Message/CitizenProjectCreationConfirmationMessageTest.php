<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\Message\CitizenProjectCreationConfirmationMessage;

/**
 * @group message
 */
class CitizenProjectCreationConfirmationMessageTest extends MessageTestCase
{
    /**
     * @var CitizenProject|null
     */
    private $citizenProject;

    public function testCreate(): void
    {
        $message = CitizenProjectCreationConfirmationMessage::create(
            $this->createAdherent('creator@example.com', 'Créateur', 'Jones'),
            $this->citizenProject,
            'https://enmarche.code/citizen-project/foo-bar/create-action'
        );

        self::assertMessage(
            CitizenProjectCreationConfirmationMessage::class,
            [
                'first_name' => 'Créateur',
                'citizen_project_name' => 'Projet Citoyen #1',
                'create_action_link' => 'https://enmarche.code/citizen-project/foo-bar/create-action',
            ],
            $message
        );

        self::assertSender(null, 'projetscitoyens@en-marche.fr', $message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'creator@example.com',
            'Créateur Jones',
            [
                'first_name' => 'Créateur',
                'citizen_project_name' => 'Projet Citoyen #1',
                'create_action_link' => 'https://enmarche.code/citizen-project/foo-bar/create-action',
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
