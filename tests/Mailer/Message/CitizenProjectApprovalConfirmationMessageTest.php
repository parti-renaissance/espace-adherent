<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\Message\CitizenProjectApprovalConfirmationMessage;

/**
 * @group message
 */
class CitizenProjectApprovalConfirmationMessageTest extends MessageTestCase
{
    /**
     * @var CitizenProject|null
     */
    private $citizenProject;

    public function testCreateFromRegistration(): void
    {
        $message = CitizenProjectApprovalConfirmationMessage::create($this->citizenProject);

        self::assertMessage(
            CitizenProjectApprovalConfirmationMessage::class,
            [
                'first_name' => 'Créateur',
                'citizen_project_name' => 'Projet Citoyen #1',
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
            ->method('getCreator')
            ->willReturn($this->createAdherent('creator@example.com', 'Créateur', 'Jones'))
        ;
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
