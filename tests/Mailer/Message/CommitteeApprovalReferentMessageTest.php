<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Mailer\Message\CommitteeApprovalReferentMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class CommitteeApprovalReferentMessageTest extends TestCase
{
    const CONTACT_LINK = 'http://enmarche.dev/espace-adherent/contacter/59b1314d-dcfb-4a4c-83e1-212841d0bd0f';

    public function testCreate()
    {
        $referent = $this->createMock(Adherent::class);
        $referent->expects($this->once())->method('getEmailAddress')->willReturn('jerome@example.com');
        $referent->expects($this->once())->method('getFullName')->willReturn('Jérôme Pichoud');
        $referent->expects($this->once())->method('getFirstName')->willReturn('Jérôme');

        $animator = $this->createMock(Adherent::class);
        $animator->expects($this->once())->method('getFirstName')->willReturn('Michel');

        $committee = $this->createMock(Committee::class);
        $committee->expects($this->once())->method('getName')->willReturn('Comité de Nice');
        $committee->expects($this->once())->method('getCityName')->willReturn('Nice');

        $message = CommitteeApprovalReferentMessage::create(
            $referent,
            $animator,
            $committee,
            self::CONTACT_LINK
        );

        $this->assertInstanceOf(CommitteeApprovalReferentMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);
        $this->assertCount(4, $message->getVars());
        $this->assertSame(
            [
                'committee_name' => 'Comité de Nice',
                'committee_city' => 'Nice',
                'animator_firstname' => 'Michel',
                'animator_contact_link' => self::CONTACT_LINK,
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
                'committee_name' => 'Comité de Nice',
                'committee_city' => 'Nice',
                'animator_firstname' => 'Michel',
                'animator_contact_link' => self::CONTACT_LINK,
                'prenom' => 'Jérôme',
            ],
            $recipient->getVars()
        );
    }
}
