<?php

namespace Tests\App\Mailer\Message;

use App\Entity\JeMarcheReport;
use App\Mailer\Message\JeMarcheReportMessage;
use PHPUnit\Framework\TestCase;

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

        $this->assertSame('Merci pour votre compte-rendu d\'action.', $message->getSubject());
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
