<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\GroupApprovalConfirmationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class GroupApprovalConfirmationMessageTest extends TestCase
{
    private const GROUP_URL = 'https://enmarche.dev/group/foo';

    public function testCreate()
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $adherent->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $message = GroupApprovalConfirmationMessage::create($adherent, 'Nice', self::GROUP_URL);

        $this->assertInstanceOf(GroupApprovalConfirmationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(2, $message->getVars());
        $this->assertSame(
            [
                'group_city' => 'Nice',
                'group_url' => self::GROUP_URL,
            ],
            $message->getVars()
        );
        $this->assertCount(1, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'group_city' => 'Nice',
                'group_url' => self::GROUP_URL,
                'animator_firstname' => 'Jérôme',
            ],
            $recipient->getVars()
        );
    }
}
