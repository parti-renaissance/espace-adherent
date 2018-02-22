<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mailer\Message\CitizenActionRegistrationConfirmationMessage;

/**
 * @group message
 */
class CitizenActionRegistrationConfirmationMessageTest extends MessageTestCase
{
    /**
     * @var EventRegistration|null
     */
    private $eventRegistration;

    public function testCreateFromRegistration(): void
    {
        $message = CitizenActionRegistrationConfirmationMessage::create(
            $this->eventRegistration,
            'https://enmarche.code/event/foo-bar/calendar'
        );

        self::assertMessage(
            CitizenActionRegistrationConfirmationMessage::class,
            [
                'first_name' => 'Jean',
                'citizen_action_name' => 'Action Citoyenne #1',
                'citizen_action_organiser' => 'Organisateur Jones',
                'citizen_action_calendar_url' => 'https://enmarche.code/event/foo-bar/calendar',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            'Jean Doe',
            [
                'first_name' => 'Jean',
                'citizen_action_name' => 'Action Citoyenne #1',
                'citizen_action_organiser' => 'Organisateur Jones',
                'citizen_action_calendar_url' => 'https://enmarche.code/event/foo-bar/calendar',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $citizenAction = $this->createMock(CitizenAction::class);

        $citizenAction
            ->expects(self::once())
            ->method('getName')
            ->willReturn('Action Citoyenne #1')
        ;
        $citizenAction
            ->expects(self::once())
            ->method('getOrganizerName')
            ->willReturn('Organisateur Jones')
        ;

        $this->eventRegistration = $this->createEventRegistration('jean@example.com', 'Jean', 'Doe');

        $this->eventRegistration
            ->expects(self::once())
            ->method('getEvent')
            ->willReturn($citizenAction)
        ;
    }

    protected function tearDown()
    {
        $this->eventRegistration = null;
    }
}
