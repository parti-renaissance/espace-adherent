<?php

namespace Tests\AppBundle\Mailjet;

use AppBundle\Mailjet\EmailTemplate;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\AppBundle\Test\Mailer\Message\DummyMessage;

class EmailTemplateTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The Mailjet email requires at least one recipient.
     */
    public function testCreateEmailTemplateWithoutRecipients()
    {
        $email = new EmailTemplate(Uuid::uuid4(), 'dummy_message', 'contact@en-marche.fr');
        $email->getBody();
    }

    public function testCreateEmailTemplate()
    {
        $email = new EmailTemplate(Uuid::uuid4(), 'dummy_message', 'contact@en-marche.fr', 'En Marche !');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);
        $email->addRecipient('vincent777h@example.tld', 'Vincent Durand', ['name' => 'Vincent Durand']);

        $body = [
            'FromEmail' => 'contact@en-marche.fr',
            'FromName' => 'En Marche !',
            'MJ-TemplateID' => 'dummy_message',
            'MJ-TemplateLanguage' => true,
            'Recipients' => [
                [
                    'Email' => 'john.smith@example.tld',
                    'Name' => 'John Smith',
                    'Vars' => [
                        'name' => 'John Smith',
                    ],
                ],
                [
                    'Email' => 'vincent777h@example.tld',
                    'Name' => 'Vincent Durand',
                    'Vars' => [
                        'name' => 'Vincent Durand',
                    ],
                ],
            ],
        ];

        $this->assertSame($body, $email->getBody());
    }

    public function testCreateEmailTemplateWithReplyTo()
    {
        $email = new EmailTemplate(Uuid::uuid4(), 'dummy_message', 'contact@en-marche.fr', 'En Marche !', 'reply@to.me');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        $body = [
            'FromEmail' => 'contact@en-marche.fr',
            'FromName' => 'En Marche !',
            'MJ-TemplateID' => 'dummy_message',
            'MJ-TemplateLanguage' => true,
            'Recipients' => [
                [
                    'Email' => 'john.smith@example.tld',
                    'Name' => 'John Smith',
                    'Vars' => [
                        'name' => 'John Smith',
                    ],
                ],
            ],
            'Headers' => [
                'Reply-To' => 'reply@to.me',
            ],
        ];

        $this->assertSame($body, $email->getBody());
    }

    public function testCreateEmailTemplateFromDummyMessage()
    {
        $email = EmailTemplate::createWithMessage(
            DummyMessage::create(),
            'dummy_message',
            'contact@en-marche.fr'
        );

        $body = [
            'FromEmail' => 'contact@en-marche.fr',
            'MJ-TemplateID' => 'dummy_message',
            'MJ-TemplateLanguage' => true,
            'Recipients' => [
                [
                    'Email' => 'dummy@example.tld',
                    'Name' => 'Dummy User',
                    'Vars' => [
                        'dummy' => 'ymmud',
                    ],
                ],
            ],
        ];

        $this->assertSame($body, $email->getBody());
    }

    public function testCreateEmailTemplateWithCc()
    {
        $email = new EmailTemplate(
            Uuid::uuid4(),
            'dummy_message',
            'contact@en-marche.fr',
            'En Marche !',
            null,
            ['"Vincent Durand" <vincent777h@example.tld>']
        );
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);
        $email->addRecipient('jane.smith@example.tld', 'Jane Smith', ['name' => 'Jane Smith']);

        $body = [
            'FromEmail' => 'contact@en-marche.fr',
            'FromName' => 'En Marche !',
            'MJ-TemplateID' => 'dummy_message',
            'MJ-TemplateLanguage' => true,
            'Vars' => [
                'name' => 'John Smith',
            ],
            'To' => '"John Smith" <john.smith@example.tld>, "Jane Smith" <jane.smith@example.tld>, "Vincent Durand" <vincent777h@example.tld>',
        ];

        $this->assertSame($body, $email->getBody());
    }
}
