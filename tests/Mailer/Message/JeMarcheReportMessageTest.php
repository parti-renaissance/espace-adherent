<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\JeMarcheReport;
use AppBundle\Mailer\Message\JeMarcheReportMessage;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class JeMarcheReportMessageTest extends TestCase
{
    public function testCreateJeMarcheReportMessageFromJeMarcheReport()
    {
        $jeMarcheReport = $this->createMock(JeMarcheReport::class);
        $jeMarcheReport->expects(static::any())->method('getEmailAddress')->willReturn('jerome.picon@gmail.tld');
        $jeMarcheReport->expects(static::any())->method('countConvinced')->willReturn(2);
        $jeMarcheReport->expects(static::any())->method('countAlmostConvinced')->willReturn(2);
        $jeMarcheReport->expects(static::any())->method('getConvincedList')->with(', ')->willReturn('test1@gmail.tld, test2@gmail.tld');
        $jeMarcheReport->expects(static::any())->method('getAlmostConvincedList')->with(', ')->willReturn('test3@gmail.tld, test4@gmail.tld');

        $message = JeMarcheReportMessage::createFromJeMarcheReport($jeMarcheReport);

        $this->assertInstanceOf(JeMarcheReportMessage::class, $message);
        $this->assertInstanceOf(UuidInterface::class, $message->getUuid());
        $this->assertCount(4, $message->getVars());
        $this->assertSame(
            [
                'nombre_emails_convaincus' => 2,
                'nombre_emails_indecis' => 2,
                'emails_collected_convaincus' => 'test1@gmail.tld, test2@gmail.tld',
                'emails_collected_indecis' => 'test3@gmail.tld, test4@gmail.tld',
            ],
            $message->getVars()
        );
    }
}
