<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailer\Message\ProcurationProxyCancelledMessage;

/**
 * @group message
 */
class ProcurationProxyCancelledMessageTest extends MessageTestCase
{
    /**
     * @var ProcurationRequest|null
     */
    private $procurationRequest;

    public function testCreate(): void
    {
        $message = ProcurationProxyCancelledMessage::create(
            $this->procurationRequest,
            $this->createAdherent('referent@example.com', 'Référent', 'Jones')
        );

        self::assertMessage(
            ProcurationProxyCancelledMessage::class,
            [
                'target_first_name' => 'Jean',
                'voter_first_name' => 'Bernard',
                'voter_last_name' => 'Smith',
            ],
            $message
        );

        self::assertSender('Procuration En Marche !', null, $message);
        self::assertReplyTo('referent@example.com', $message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            null,
            [
                'target_first_name' => 'Jean',
                'voter_first_name' => 'Bernard',
                'voter_last_name' => 'Smith',
            ],
            $message
        );

        self::assertCountCC(2, $message);
        self::assertMessageCC('bernard@example.com', $message);
        self::assertMessageCC('referent@example.com', $message);
    }

    protected function setUp()
    {
        $procurationProxy = $this->createMock(ProcurationProxy::class);

        $procurationProxy
            ->expects(self::once())
            ->method('getEmailAddress')
            ->willReturn('bernard@example.com')
        ;
        $procurationProxy
            ->expects(self::once())
            ->method('getFirstNames')
            ->willReturn('Bernard')
        ;
        $procurationProxy
            ->expects(self::once())
            ->method('getLastName')
            ->willReturn('Smith')
        ;

        $this->procurationRequest = $this->createMock(ProcurationRequest::class);

        $this->procurationRequest
            ->expects(self::once())
            ->method('getEmailAddress')
            ->willReturn('jean@example.com')
        ;
        $this->procurationRequest
            ->expects(self::once())
            ->method('getFirstNames')
            ->willReturn('Jean')
        ;
        $this->procurationRequest
            ->expects(self::once())
            ->method('getFoundProxy')
            ->willReturn($procurationProxy)
        ;
    }

    protected function tearDown()
    {
        $this->procurationRequest = null;
    }
}
