<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Committee;
use AppBundle\Mailer\Message\CommitteeNewFollowerMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;

class CommitteeNewFollowerMessageTest extends AbstractEventMessageTest
{
    public function testCreate()
    {
        $hosts[] = $this->createAdherentMock('em@example.com', 'Émmanuel', 'Macron');
        $hosts[] = $this->createAdherentMock('jb@example.com', 'Jean', 'Berenger');
        $hosts[] = $this->createAdherentMock('ml@example.com', 'Marie', 'Lambert');
        $hosts[] = $this->createAdherentMock('ez@example.com', 'Éric', 'Zitrone');

        $newFollower = $this->createAdherentMock('jerome@example.com', 'Jérôme', 'Pichoud');
        $newFollower->expects($this->once())->method('getAge')->willReturn(32);
        $newFollower->expects($this->once())->method('getCityName')->willReturn('Nice');
        $newFollower->expects($this->once())->method('getLastNameInitial')->willReturn('P.');

        $committee = $this->createMock(Committee::class);
        $committee->expects($this->once())->method('getName')->willReturn('Comité de Nice');

        $message = CommitteeNewFollowerMessage::create($committee, $hosts, $newFollower);

        $this->assertInstanceOf(CommitteeNewFollowerMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame('jerome@example.com', $message->getReplyTo());
        $this->assertCount(5, $message->getVars());
        $this->assertSame(
            [
                'committee_name' => 'Comité de Nice',
                'member_firstname' => 'Jérôme',
                'member_lastname' => 'P.',
                'member_city' => 'Nice',
                'member_age' => 32,
            ],
            $message->getVars()
        );
        $this->assertCount(4, $message->getRecipients());

        $recipient = $message->getRecipient(0);

        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('em@example.com', $recipient->getEmailAddress());
        $this->assertSame('Émmanuel Macron', $recipient->getFullName());
        $this->assertSame(
            [
                'committee_name' => 'Comité de Nice',
                'member_firstname' => 'Jérôme',
                'member_lastname' => 'P.',
                'member_city' => 'Nice',
                'member_age' => 32,
                'animator_firstname' => 'Émmanuel',
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(3);

        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('ez@example.com', $recipient->getEmailAddress());
        $this->assertSame('Éric Zitrone', $recipient->getFullName());
        $this->assertSame(
            [
                'committee_name' => 'Comité de Nice',
                'member_firstname' => 'Jérôme',
                'member_lastname' => 'P.',
                'member_city' => 'Nice',
                'member_age' => 32,
                'animator_firstname' => 'Éric',
            ],
            $recipient->getVars()
        );
    }
}
