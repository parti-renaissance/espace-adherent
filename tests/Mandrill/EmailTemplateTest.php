<?php

declare(strict_types=1);

namespace Tests\App\Mandrill;

use App\Mailer\EmailTemplateFactory;
use App\Mailer\Template\Manager;
use App\Mandrill\EmailTemplate;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\App\Test\Mailer\Message\DummyMessage;

class EmailTemplateTest extends TestCase
{
    public function testCreateEmailTemplateWithoutRecipients()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The email requires at least one recipient.');

        $email = new EmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr');
        $email->getBody();
    }

    public function testCreateEmailTemplate()
    {
        $email = new EmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr', 'En Marche !');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);
        $email->addRecipient('vincent777h@example.tld', 'Vincent Durand', ['name' => 'Vincent Durand']);

        $body = [
            'template_name' => '12345',
            'template_content' => [],
            'message' => [
                'subject' => 'Votre donation !',
                'from_email' => 'contact@en-marche.fr',
                'html' => null,
                'merge_vars' => [
                    [
                        'rcpt' => 'john.smith@example.tld',
                        'vars' => [
                            [
                                'name' => 'name',
                                'content' => 'John Smith',
                            ],
                        ],
                    ],
                    [
                        'rcpt' => 'vincent777h@example.tld',
                        'vars' => [
                            [
                                'name' => 'name',
                                'content' => 'Vincent Durand',
                            ],
                        ],
                    ],
                ],
                'from_name' => 'En Marche !',
                'to' => [
                    [
                        'email' => 'john.smith@example.tld',
                        'type' => 'to',
                        'name' => 'John Smith',
                    ],
                    [
                        'email' => 'vincent777h@example.tld',
                        'type' => 'to',
                        'name' => 'Vincent Durand',
                    ],
                ],
            ],
        ];

        $this->assertSame($body, $email->getBody());
    }

    public function testCreateEmailTemplateWithReplyTo()
    {
        $email = new EmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr', 'En Marche !', 'reply@to.me');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        $body = [
            'template_name' => '12345',
            'template_content' => [],
            'message' => [
                'subject' => 'Votre donation !',
                'from_email' => 'contact@en-marche.fr',
                'html' => null,
                'merge_vars' => [
                    [
                        'rcpt' => 'john.smith@example.tld',
                        'vars' => [
                            [
                                'name' => 'name',
                                'content' => 'John Smith',
                            ],
                        ],
                    ],
                ],
                'headers' => [
                    'Reply-To' => 'reply@to.me',
                ],
                'from_name' => 'En Marche !',
                'to' => [
                    [
                        'email' => 'john.smith@example.tld',
                        'type' => 'to',
                        'name' => 'John Smith',
                    ],
                ],
            ],
        ];

        $this->assertSame($body, $email->getBody());
    }

    public function testCreateEmailTemplateFromDummyMessage()
    {
        $emailTemplateFactory = new EmailTemplateFactory(
            'sender@test.com',
            'Test sender',
            $this->createMock(Manager::class)
        );

        $message = DummyMessage::create();
        $message->setSenderEmail('contact@en-marche.fr');
        $email = $emailTemplateFactory->createFromMessage($message);

        $body = [
            'template_name' => '66666',
            'template_content' => [],
            'message' => [
                'subject' => 'Dummy Message',
                'from_email' => 'contact@en-marche.fr',
                'html' => null,
                'global_merge_vars' => [
                    [
                        'name' => 'dummy',
                        'content' => 'ymmud',
                    ],
                ],
                'from_name' => 'Test sender',
                'to' => [
                    [
                        'email' => 'dummy@example.tld',
                        'type' => 'to',
                        'name' => 'Dummy User',
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
            '12345',
            'Votre donation !',
            'contact@en-marche.fr',
            'En Marche !',
            null,
            ['vincent777h@example.tld']
        );
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);
        $email->addRecipient('jane.smith@example.tld', 'Jane Smith', ['name' => 'Jane Smith']);

        $body = [
            'template_name' => '12345',
            'template_content' => [],
            'message' => [
                'subject' => 'Votre donation !',
                'from_email' => 'contact@en-marche.fr',
                'html' => null,
                'merge_vars' => [
                    [
                        'rcpt' => 'john.smith@example.tld',
                        'vars' => [
                            [
                                'name' => 'name',
                                'content' => 'John Smith',
                            ],
                        ],
                    ],
                    [
                        'rcpt' => 'jane.smith@example.tld',
                        'vars' => [
                            [
                                'name' => 'name',
                                'content' => 'Jane Smith',
                            ],
                        ],
                    ],
                ],
                'from_name' => 'En Marche !',
                'to' => [
                    [
                        'email' => 'john.smith@example.tld',
                        'type' => 'to',
                        'name' => 'John Smith',
                    ],
                    [
                        'email' => 'jane.smith@example.tld',
                        'type' => 'to',
                        'name' => 'Jane Smith',
                    ],
                    [
                        'email' => 'vincent777h@example.tld',
                        'type' => 'cc',
                    ],
                ],
            ],
        ];

        $this->assertSame($body, $email->getBody());
    }
}
