<?php

declare(strict_types=1);

namespace Tests\App\Mailer\Transport;

use App\Entity\Email\EmailLog;
use App\Mailer\Command\AsyncSendMessageCommand;
use App\Mailer\EmailTemplateFactory;
use App\Mailer\Handler\SendMessageCommandHandler;
use App\Mailer\Message\Message;
use App\Mailer\Template\Manager;
use App\Mailer\Transport\MessengerTransport;
use App\Mandrill\EmailTemplate;
use App\Repository\Email\EmailLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\App\Test\Mailer\DummyEmailClient;

class MessengerTransportTest extends TestCase
{
    public function testCannotSendTemplateEmail()
    {
        $this->expectException(HandlerFailedException::class);
        $this->expectExceptionMessageMatches('#.+Unable to send email to recipients\.#');
        $httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $httpClient->expects($this->once())->method('request')->willReturn(new MockResponse('', ['http_code' => 400]));

        /** @var EmailTemplate $email */
        [$message, $email] = $this->createDummyEmail();

        $emailRepository = $this->getMockBuilder(EmailLogRepository::class)->disableOriginalConstructor()->getMock();
        $emailRepository->expects($this->once())->method('findOneByUuid')->willReturn(EmailLog::createFromMessage($message, $email->getHttpRequestPayload()));
        $client = new DummyEmailClient($httpClient);

        $transport = new MessengerTransport($this->getBus(new SendMessageCommandHandler($emailRepository, $client)));
        $transport->sendTemplateEmail($email);
    }

    public function testSendTemplateEmail()
    {
        /** @var EmailTemplate $email */
        [$message, $email] = $this->createDummyEmail();

        $body = <<<'EOF'
            {
                "Sent": [
                    {
                        "Email": "john.smith@example.tld",
                        "MessageID": 111111111111111
                    }
                ]
            }
            EOF;

        $httpClient = new MockHttpClient([new MockResponse($body)], 'http://null');
        $client = new DummyEmailClient($httpClient);

        $emailRepository = $this->createPartialMock(EmailLogRepository::class, ['findOneByUuid', 'getEntityManager']);
        $emailRepository->expects($this->once())->method('findOneByUuid')->willReturn($emailObject = EmailLog::createFromMessage($message, $email->getHttpRequestPayload()));
        $emailRepository->expects($this->once())->method('getEntityManager')->willReturn($this->createMock(EntityManagerInterface::class));

        $transport = new MessengerTransport($this->getBus(new SendMessageCommandHandler($emailRepository, $client)));
        $transport->sendTemplateEmail($email);

        $this->assertSame($body, $emailObject->getResponsePayloadJson());
    }

    private function createDummyEmail(): array
    {
        $message = new Message(Uuid::uuid4(), 'recipient@test.com', 'FirstName', 'Votre donation !', [], [], 'contact@en-marche.fr', 'En Marche !');
        $message->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        $emailTemplateFactory = new EmailTemplateFactory(
            'sender@test.com',
            'Test sender',
            $this->createMock(Manager::class)
        );
        $message->setSenderEmail('contact@en-marche.fr');
        $email = $emailTemplateFactory->createFromMessage($message);

        return [$message, $email];
    }

    private function getBus(object $handler): MessageBusInterface
    {
        return new MessageBus([new HandleMessageMiddleware(new HandlersLocator([
            AsyncSendMessageCommand::class => [$handler],
        ]))]);
    }
}
