<?php

namespace Tests\AppBundle\Mailjet\Transport;

use Ramsey\Uuid\Uuid;
use Tests\AppBundle\Test\Mailjet\Transport\NullTransport;
use AppBundle\Mailjet\EmailTemplate;
use Psr\Log\LoggerInterface;

class NullTransportTest extends \PHPUnit_Framework_TestCase
{
    public function testSendTemplateEmail()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('[mailjet] sending email with Mailjet.')
        ;

        $email = new EmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr', 'En Marche !');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        $transport = new NullTransport($logger);
        $transport->sendTemplateEmail($email);
    }
}
