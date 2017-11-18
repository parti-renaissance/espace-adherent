<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\BoardMember\BoardMemberMessage as BoardMemberMessageModel;
use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\BoardMemberMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class BoardMemberMessageTest extends TestCase
{
    public function testCreateFromModel()
    {
        $adherent = $this->createMock(Adherent::class);
        $adherent->expects($this->once())->method('getEmailAddress')->willReturn('kevin@ouin.com');
        $adherent->expects($this->once())->method('getFullName')->willReturn('Kévin OUIN');

        $model = $this->createMock(BoardMemberMessageModel::class);
        $model->expects($this->once())->method('getFrom')->willReturn($adherent);
        $model->expects($this->once())->method('getContent')->willReturn('foo');

        $recipient = $this->createMock(Adherent::class);
        $recipient->expects($this->once())->method('getEmailAddress')->willReturn('thomas@chouchou.com');
        $recipient->expects($this->once())->method('getFullName')->willReturn('Thomas CHOUCHOU');

        $message = BoardMemberMessage::createFromModel($model, [$recipient]);

        $this->assertInstanceOf(BoardMemberMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(1, $message->getVars());
        $this->assertSame(
            [
                'target_message' => 'foo',
            ],
            $message->getVars()
        );
        $this->assertCount(2, $message->getRecipients());

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('thomas@chouchou.com', $recipient->getEmailAddress());
        $this->assertSame('Thomas CHOUCHOU', $recipient->getFullName());
        $this->assertSame(
            [
                'target_message' => 'foo',
            ],
            $recipient->getVars()
        );

        $recipient = $message->getRecipient(1);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jemarche@en-marche.fr', $recipient->getEmailAddress());
        $this->assertSame('Je Marche', $recipient->getFullName());
        $this->assertSame(
            [
                'target_message' => 'foo',
            ],
            $recipient->getVars()
        );
    }
}
