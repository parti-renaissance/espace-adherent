<?php

namespace Tests\AppBundle\Mailer\Transport;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\AppBundle\Test\Mailer\Transport\NullTransport;
use AppBundle\Mailjet\EmailTemplate;
use Psr\Log\LoggerInterface;

class NullTransportTest extends TestCase
{
    public function testSendTemplateEmail()
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger
            ->expects($this->once())
            ->method('info')
            ->with('[mailer] sending email.')
        ;

        $email = new EmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr', 'En Marche !');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        $transport = new NullTransport($logger);
        $transport->sendTemplateEmail($email);
    }
}
