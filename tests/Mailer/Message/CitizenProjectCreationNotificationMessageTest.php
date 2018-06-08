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
            'https://bar.foo'
        );

        self::assertMessage(
            CitizenProjectCreationNotificationMessage::class,
            [
                'first_name' => 'Bernard',
                'citizen_project_list' => 'Projet Citoyen #1',
                'all_citizen_projects_url' => 'https://bar.foo',
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
                'citizen_project_list' => 'Projet Citoyen #1',
                'all_citizen_projects_url' => 'https://bar.foo',
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
