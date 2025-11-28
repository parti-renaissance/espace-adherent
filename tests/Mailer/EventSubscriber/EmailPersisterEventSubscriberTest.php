<?php

declare(strict_types=1);

namespace Tests\App\Mailer\EventSubscriber;

use App\Entity\Email\EmailLog;
use App\Mailer\EmailTemplateFactory;
use App\Mailer\Event\MailerEvent;
use App\Mailer\EventSubscriber\EmailPersisterEventSubscriber;
use App\Mailer\Message\Renaissance\RenaissanceDeclaredMandateNotificationMessage;
use App\Mailer\Template\Manager;
use App\Repository\Email\EmailLogRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use PHPUnit\Framework\TestCase;
use Tests\App\Test\Mailer\Message\DummyMessage;

class EmailPersisterEventSubscriberTest extends TestCase
{
    /** @var EmailPersisterEventSubscriber */
    private $subscriber;
    private $repository;
    private $manager;
    private $emailTemplateFactory;

    public function testOnMailerDeliveryMessage()
    {
        $this->manager->expects($this->once())->method('persist');
        $this->manager->expects($this->once())->method('flush');
        $this->manager->expects($this->once())->method('detach');

        $message = RenaissanceDeclaredMandateNotificationMessage::create(['john@smith.tld', 'johana156@gmail.com'], [1, 2], 'https://');
        $message->setSenderEmail('noreply@en-marche.fr');

        $this->subscriber->onMailerDeliveryMessage(new MailerEvent(
            $message,
            $this->emailTemplateFactory->createFromMessage($message)
        ));
    }

    public function testOnMailerDeliverySuccessWithEmptyJsonResponsePayload()
    {
        $this->repository->expects($this->never())->method('findOneByUuid');
        $this->manager->expects($this->never())->method('flush');
        $message = DummyMessage::create();
        $message->setSenderEmail('noreply@en-marche.fr');
        $this->subscriber->onMailerDeliverySuccess(new MailerEvent(
            $message,
            $this->emailTemplateFactory->createFromMessage($message)
        ));
    }

    public function testOnMailerDeliverySuccessWithEmptyMessageCollection()
    {
        $responsePayload = <<<'EOF'
            {
                "Sent": [
                    {"Email": "dummy@example.tld", "MessageID": 111111111111111}
                ]
            }
            EOF;

        $message = DummyMessage::create();
        $message->setSenderEmail('noreply@en-marche.fr');
        $email = $this->emailTemplateFactory->createFromMessage($message);
        $email->delivered($responsePayload, $email->getHttpRequestPayload());

        $this->repository->expects($this->once())->method('findOneByUuid')->willReturn(null);
        $this->manager->expects($this->never())->method('flush');

        $this->subscriber->onMailerDeliverySuccess(new MailerEvent($message, $email));
    }

    public function testOnMailerDeliverySuccess()
    {
        $message = DummyMessage::create();
        $message->addRecipient('vincent777h@example.tld', 'Vincent Durand');

        $responsePayload = <<<'EOF'
            {
                "Sent": [
                    {"Email": "dummy@example.tld", "MessageID": 111111111111111},
                    {"Email": "vincent777h@example.tld", "MessageID": 222222222222222}
                ]
            }
            EOF;

        $message->setSenderEmail('noreply@en-marche.fr');
        $email = $this->emailTemplateFactory->createFromMessage($message);
        $email->delivered($responsePayload, $requestPayload = $email->getHttpRequestPayload());

        $this->repository
            ->expects($this->once())
            ->method('findOneByUuid')
            ->willReturn($message1 = EmailLog::createFromMessage($message, $requestPayload))
        ;

        $this->manager->expects($this->once())->method('flush');

        $this->subscriber->onMailerDeliverySuccess(new MailerEvent($message, $email));

        $this->assertTrue($message1->isDelivered());
        $this->assertSame($requestPayload, $message1->getRequestPayloadJson());
        $this->assertSame($responsePayload, $message1->getResponsePayloadJson());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = $this->getMockBuilder(ObjectManager::class)->getMock();
        $this->repository = $this->getMockBuilder(EmailLogRepository::class)->disableOriginalConstructor()->getMock();

        $this->repository->expects($this->any())->method('getClassName')->willReturn(EmailLog::class);

        $this->emailTemplateFactory = new EmailTemplateFactory(
            'sender@test.com',
            'Test sender',
            $this->createMock(Manager::class)
        );

        $this->subscriber = new EmailPersisterEventSubscriber(
            $this->manager,
            $this->repository
        );
    }

    protected function tearDown(): void
    {
        $this->manager = null;
        $this->repository = null;
        $this->subscriber = null;
        $this->emailTemplateFactory = null;

        parent::tearDown();
    }
}
