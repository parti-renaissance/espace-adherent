<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenProject;
use AppBundle\Mailer\Message\CitizenProjectRequestCommitteeSupportMessage;

/**
 * @group message
 */
class CitizenProjectRequestCommitteeSupportMessageTest extends MessageTestCase
{
    /**
     * @var CitizenProject|null
     */
    private $citizenProject;

    public function testCreate(): void
    {
        $message = CitizenProjectRequestCommitteeSupportMessage::create(
            $this->citizenProject,
            $this->createAdherent('supervisor@example.com', 'Superviseur', 'Jones'),
            'https://enmarche.code/projet-citoyen/foo-bar/support'
        );

        self::assertMessage(
            CitizenProjectRequestCommitteeSupportMessage::class,
            [
                'citizen_project_name' => 'Projet Citoyen #1',
                'citizen_project_host_first_name' => 'Bernard',
                'citizen_project_host_last_name' => 'Smith',
                'citizen_project_committee_support_url' => 'https://enmarche.code/projet-citoyen/foo-bar/support',
            ],
            $message
        );

        self::assertSender(null, 'projetscitoyens@en-marche.fr', $message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'supervisor@example.com',
            'Superviseur Jones',
            [
                'citizen_project_name' => 'Projet Citoyen #1',
                'citizen_project_host_first_name' => 'Bernard',
                'citizen_project_host_last_name' => 'Smith',
                'citizen_project_committee_support_url' => 'https://enmarche.code/projet-citoyen/foo-bar/support',
                'recipient_first_name' => 'Superviseur',
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
            ->method('getCreator')
            ->willReturn($this->createAdherent('bernard@example.com', 'Bernard', 'Smith'))
        ;
    }

    protected function tearDown()
    {
        $this->citizenProject = null;
    }
}
