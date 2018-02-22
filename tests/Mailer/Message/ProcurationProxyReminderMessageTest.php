<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Mailer\Message\ProcurationProxyReminderMessage;

/**
 * @group message
 */
class ProcurationProxyReminderMessageTest extends MessageTestCase
{
    /**
     * @var ProcurationRequest|null
     */
    private $procurationRequest;

    public function testCreate(): void
    {
        $message = ProcurationProxyReminderMessage::create(
            $this->procurationRequest,
            'https://enmarche.code/procurations/informations'
        );

        self::assertMessage(
            ProcurationProxyReminderMessage::class,
            [
                'first_name' => 'Jean',
                'info_link' => 'https://enmarche.code/procurations/informations',
                'elections' => '1er tour, 2eme tour',
                'voter_first_name' => 'Bernard',
                'voter_last_name' => 'Smith',
                'voter_phone' => '06 12 34 56 78',
                'mandant_first_name' => 'Jean',
                'mandant_last_name' => 'Doe',
                'mandant_phone' => '06 87 65 43 21',
            ],
            $message
        );

        self::assertSender('Procuration En Marche !', null, $message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(2, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            null,
            [
                'first_name' => 'Jean',
                'info_link' => 'https://enmarche.code/procurations/informations',
                'elections' => '1er tour, 2eme tour',
                'voter_first_name' => 'Bernard',
                'voter_last_name' => 'Smith',
                'voter_phone' => '06 12 34 56 78',
                'mandant_first_name' => 'Jean',
                'mandant_last_name' => 'Doe',
                'mandant_phone' => '06 87 65 43 21',
            ],
            $message
        );

        self::assertMessageRecipient(
            'bernard@example.com',
            null,
            [
                'first_name' => 'Jean',
                'info_link' => 'https://enmarche.code/procurations/informations',
                'elections' => '1er tour, 2eme tour',
                'voter_first_name' => 'Bernard',
                'voter_last_name' => 'Smith',
                'voter_phone' => '06 12 34 56 78',
                'mandant_first_name' => 'Jean',
                'mandant_last_name' => 'Doe',
                'mandant_phone' => '06 87 65 43 21',
            ],
            $message
        );

        self::assertNoCC($message);
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
        $procurationProxy
            ->expects(self::once())
            ->method('getPhone')
            ->willReturn(self::createPhoneNumber('06 12 34 56 78'))
        ;

        $this->procurationRequest = $this->createMock(ProcurationRequest::class);

        $this->procurationRequest
            ->expects(self::once())
            ->method('getEmailAddress')
            ->willReturn('jean@example.com')
        ;
        $this->procurationRequest
            ->expects(self::exactly(2))
            ->method('getFirstNames')
            ->willReturn('Jean')
        ;
        $this->procurationRequest
            ->expects(self::once())
            ->method('getLastName')
            ->willReturn('Doe')
        ;
        $this->procurationRequest
            ->expects(self::once())
            ->method('getPhone')
            ->willReturn(self::createPhoneNumber('06 87 65 43 21'))
        ;
        $this->procurationRequest
            ->expects(self::once())
            ->method('getElectionRoundLabels')
            ->willReturn(['1er tour', '2eme tour'])
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
