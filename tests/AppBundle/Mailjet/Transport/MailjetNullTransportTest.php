<?php

namespace Tests\AppBundle\Mailjet\Transport;

use AppBundle\Mailjet\Transport\MailjetNullTransport;
use AppBundle\Mailjet\MailjetTemplateEmail;
use Psr\Log\LoggerInterface;

class MailjetNullTransportTest extends \PHPUnit_Framework_TestCase
{
    public function testSendTemplateEmail()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('[mailjet] sending email with Mailjet.')
        ;

        $email = new MailjetTemplateEmail('12345', 'Votre donation !', 'contact@en-marche.fr', 'En Marche !');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        $transport = new MailjetNullTransport($logger);
        $transport->sendTemplateEmail($email);
    }
}
