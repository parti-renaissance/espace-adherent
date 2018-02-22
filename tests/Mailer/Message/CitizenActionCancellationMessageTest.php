<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenAction;
use AppBundle\Mailer\Message\CitizenActionCancellationMessage;

/**
 * @group message
 */
class CitizenActionCancellationMessageTest extends MessageTestCase
{
    /**
     * @var CitizenAction|null
     */
    private $citizenAction;

    public function testCreate(): void
    {
        $message = CitizenActionCancellationMessage::create(
            $this->citizenAction,
            $this->createAdherent('author@example.com', 'Auteur', 'Jones'),
            [
                $this->createEventRegistration('jean@example.com', 'Jean', 'Doe'),
                $this->createEventRegistration('bernard@example.com', 'Bernard', 'Smith'),
            ]
        );

        self::assertMessage(
            CitizenActionCancellationMessage::class,
            [
                'citizen_action_name' => 'Action Citoyenne #1',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertReplyTo('author@example.com', $message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'citizen_action_name' => 'Action Citoyenne #1',
                'first_name' => 'Jean',
            ],
            $message
        );
        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'citizen_action_name' => 'Action Citoyenne #1',
                'first_name' => 'Bernard',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->citizenAction = $this->createMock(CitizenAction::class);

        $this->citizenAction
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Action Citoyenne #1')
        ;
    }

    protected function tearDown()
    {
        $this->citizenAction = null;
    }
}
