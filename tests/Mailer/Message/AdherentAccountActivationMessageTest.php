<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Adherent;
use AppBundle\Mailer\Message\AdherentAccountActivationMessage;
use AppBundle\Mailer\Message\Message;
use AppBundle\Mailer\Message\MessageRecipient;
use PHPUnit\Framework\TestCase;

class AdherentAccountActivationMessageTest extends TestCase
{
    private const CONFIRMATION_URL = 'https://test.enmarche.code/activation';

    /**
     * @var Adherent|null
     */
    private $adherent;

    public function testCreateFromAdherent(): void
    {
        $message = AdherentAccountActivationMessage::createFromAdherent($this->adherent, self::CONFIRMATION_URL);

        $this->assertInstanceOf(AdherentAccountActivationMessage::class, $message);
        $this->assertInstanceOf(Message::class, $message);

        $this->assertCount(2, $message->getVars());
        $this->assertSame(
            [
                'first_name' => 'Jérôme',
                'activation_url' => self::CONFIRMATION_URL,
            ],
            $message->getVars()
        );

        $recipient = $message->getRecipient(0);
        $this->assertInstanceOf(MessageRecipient::class, $recipient);
        $this->assertSame('jerome@example.com', $recipient->getEmailAddress());
        $this->assertSame('Jérôme Pichoud', $recipient->getFullName());
        $this->assertSame(
            [
                'first_name' => 'Jérôme',
                'activation_url' => self::CONFIRMATION_URL,
            ],
            $recipient->getVars()
        );
    }

    protected function setUp()
    {
        $this->adherent = $this->createMock(Adherent::class);

        $this->adherent
            ->expects($this->once())
            ->method('getEmailAddress')
            ->willReturn('jerome@example.com')
        ;
        $this->adherent
            ->expects($this->once())
            ->method('getFullName')
            ->willReturn('Jérôme Pichoud')
        ;
        $this->adherent
            ->expects($this->once())
            ->method('getFirstName')
            ->willReturn('Jérôme')
        ;
    }

    protected function tearDown()
    {
        $this->adherent = null;
    }
}
