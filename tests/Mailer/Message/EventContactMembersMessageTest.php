<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\EventRegistration;
use AppBundle\Mailer\Message\EventContactMembersMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;

class EventContactMembersMessageTest extends AbstractEventMessageTest
{
    public function testCreateEventCancellationMessage()
    {
        $recipients[] = $this->createEventRegistrationMock('em@example.com', 'Émmanuel');
        $recipients[] = $this->createEventRegistrationMock('jb@example.com', 'Jean');
        $recipients[] = $this->createEventRegistrationMock('ml@example.com', 'Marie');
        $recipients[] = $this->createEventRegistrationMock('ez@example.com', 'Éric');

        $organizer = $this->createAdherentMock('jerome@example.com', 'Jérôme', 'Pichoud');

        $message = EventContactMembersMessage::create(
            $recipients,
            $organizer,
            'I like trains.'
        );

        $this->assertInstanceOf(EventContactMembersMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(2, $message->getVars());
        $this->assertSame(
            [
                'organizer_firstname' => 'Jérôme',
                'target_message' => 'I like trains.',
            ],
            $message->getVars()
        );
        $this->assertCount(4, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel', $recipient->getFullName());
        $this->assertSame(
            [
                'organizer_firstname' => 'Jérôme',
                'target_message' => 'I like trains.',
                'target_firstname' => 'Émmanuel',
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(3);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric', $recipient->getFullName());
        $this->assertSame(
            [
                'organizer_firstname' => 'Jérôme',
                'target_message' => 'I like trains.',
                'target_firstname' => 'Éric',
            ],
            $recipient->getVars()
        );
    }

    private function createEventRegistrationMock(
        string $emailAddress,
        string $firstName
    ): EventRegistration {
        $eventRegistration = $this->createMock(EventRegistration::class);

        $eventRegistration->expects($this->any())->method('getEmailAddress')->willReturn($emailAddress);
        $eventRegistration->expects($this->any())->method('getFirstName')->willReturn($firstName);

        return $eventRegistration;
    }
}
