<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\PurchasingPowerInvitation;
use AppBundle\Mailer\Message\PurchasingPowerMessage;

/**
 * @group message
 */
class PurchasingPowerMessageTest extends MessageTestCase
{
    /**
     * @var PurchasingPowerInvitation|null
     */
    private $purchasingPowerInvitation;

    public function testCreate(): void
    {
        $message = PurchasingPowerMessage::create($this->purchasingPowerInvitation);

        self::assertMessage(
            PurchasingPowerMessage::class,
            [
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertSender('Bernard Smith', null, $message);
        self::assertReplyTo('bernard@example.com', $message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            null,
            [
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertCountCC(1, $message);
        self::assertMessageCC('bernard@example.com', $message);
    }

    protected function setUp()
    {
        $this->purchasingPowerInvitation = $this->createMock(PurchasingPowerInvitation::class);

        $this->purchasingPowerInvitation
            ->expects(self::once())
            ->method('getFriendEmailAddress')
            ->willReturn('jean@example.com')
        ;
        $this->purchasingPowerInvitation
            ->expects(self::exactly(2))
            ->method('getAuthorEmailAddress')
            ->willReturn('bernard@example.com')
        ;
        $this->purchasingPowerInvitation
            ->expects(self::once())
            ->method('getAuthorFirstName')
            ->willReturn('Bernard')
        ;
        $this->purchasingPowerInvitation
            ->expects(self::once())
            ->method('getAuthorLastName')
            ->willReturn('Smith')
        ;
        $this->purchasingPowerInvitation
            ->expects(self::once())
            ->method('getMailBody')
            ->willReturn('Contenu du message de test.')
        ;
    }

    protected function tearDown()
    {
        $this->purchasingPowerInvitation = null;
    }
}
