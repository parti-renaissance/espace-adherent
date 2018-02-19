<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\JeMarcheReport;
use AppBundle\Mailer\Message\JeMarcheReportMessage;

/**
 * @group message
 */
class JeMarcheReportMessageTest extends MessageTestCase
{
    /**
     * @var JeMarcheReport|null
     */
    private $jeMarcheReport;

    public function testCreate(): void
    {
        $message = JeMarcheReportMessage::create($this->jeMarcheReport);

        self::assertMessage(
            JeMarcheReportMessage::class,
            [
                'nombre_emails_convaincus' => 3,
                'nombre_emails_indecis' => 2,
                'emails_collected_convaincus' => 'remi@example.com, jean@example.com, bernard@example.com',
                'emails_collected_indecis' => 'john@example.com, doe@example.com',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'recipient@example.com',
            null,
            [
                'nombre_emails_convaincus' => 3,
                'nombre_emails_indecis' => 2,
                'emails_collected_convaincus' => 'remi@example.com, jean@example.com, bernard@example.com',
                'emails_collected_indecis' => 'john@example.com, doe@example.com',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->jeMarcheReport = $this->createMock(JeMarcheReport::class);

        $this->jeMarcheReport
            ->expects(self::once())
            ->method('getEmailAddress')
            ->willReturn('recipient@example.com')
        ;
        $this->jeMarcheReport
            ->expects(self::once())
            ->method('countConvinced')
            ->willReturn(3)
        ;
        $this->jeMarcheReport
            ->expects(self::once())
            ->method('countAlmostConvinced')
            ->willReturn(2)
        ;
        $this->jeMarcheReport
            ->expects(self::once())
            ->method('getConvincedList')
            ->willReturn('remi@example.com, jean@example.com, bernard@example.com')
        ;
        $this->jeMarcheReport
            ->expects(self::once())
            ->method('getAlmostConvincedList')
            ->willReturn('john@example.com, doe@example.com')
        ;
    }

    protected function tearDown()
    {
        $this->jeMarcheReport = null;
    }
}
