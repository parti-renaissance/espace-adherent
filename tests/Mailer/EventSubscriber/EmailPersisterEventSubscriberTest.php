<?php

namespace Tests\App\Mailer\EventSubscriber;

use App\Entity\Adherent;
use App\Entity\CommitteeFeedItem;
use App\Entity\Email\EmailLog;
use App\Mailer\EmailTemplateFactory;
use App\Mailer\Event\MailerEvent;
use App\Mailer\EventSubscriber\EmailPersisterEventSubscriber;
use App\Mailer\Message\CommitteeMessageNotificationMessage;
use App\Mailer\Template\Manager;
use App\Repository\Email\EmailLogRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
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

        $adherents[] = $author = $this->createAdherentMock('john@smith.tld', 'John', 'Smith');
        $adherents[] = $this->createAdherentMock('johana156@gmail.com', 'Johana', 'Durand');

        $item = $this->createCommitteeFeedItemMock('25c5762d-5a50-4c68-8f6c-106bcbff862e', 'Un message !', $author);

        $message = CommitteeMessageNotificationMessage::create($adherents, $item, 'Foo subject');
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

    private function createAdherentMock(string $emailAddress, string $firstName, string $lastName)
    {
        $mock = $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getEmailAddress')->willReturn($emailAddress);
        $mock->expects($this->any())->method('getFirstName')->willReturn($firstName);
        $mock->expects($this->any())->method('getFullName')->willReturn($firstName.' '.$lastName);

        return $mock;
    }

    private function createCommitteeFeedItemMock(string $uuid, string $content, Adherent $author)
    {
        $mock = $this->getMockBuilder(CommitteeFeedItem::class)->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())->method('getUuid')->willReturn(Uuid::fromString($uuid));
        $mock->expects($this->any())->method('getAuthorFirstName')->willReturn($author->getFirstName());
        $mock->expects($this->any())->method('getContent')->willReturn($content);
        $mock->expects($this->any())->method('getAuthor')->willReturn($author);

        return $mock;
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
