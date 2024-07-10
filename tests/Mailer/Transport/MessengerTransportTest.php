<?php

namespace Tests\App\Mailer\Transport;

use App\Entity\Email;
use App\Mailer\Command\AsyncSendMessageCommand;
use App\Mailer\Handler\SendMessageCommandHandler;
use App\Mailer\Message\Message;
use App\Mailer\Transport\MessengerTransport;
use App\Mandrill\EmailTemplate;
use App\Repository\EmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\App\Test\Mailer\DummyEmailClient;
use Tests\App\Test\Mailer\DummyEmailTemplate;

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

        $emailRepository = $this->getMockBuilder(EmailRepository::class)->disableOriginalConstructor()->getMock();
        $emailRepository->expects($this->once())->method('findOneByUuid')->willReturn(Email::createFromMessage($message, $email->getHttpRequestPayload()));
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

        $emailRepository = $this->createPartialMock(EmailRepository::class, ['findOneByUuid', 'getEntityManager']);
        $emailRepository->expects($this->once())->method('findOneByUuid')->willReturn($emailObject = Email::createFromMessage($message, $email->getHttpRequestPayload()));
        $emailRepository->expects($this->once())->method('getEntityManager')->willReturn($this->createMock(EntityManagerInterface::class));

        $transport = new MessengerTransport($this->getBus(new SendMessageCommandHandler($emailRepository, $client)));
        $transport->sendTemplateEmail($email);

        $this->assertSame($body, $emailObject->getResponsePayloadJson());
    }

    private function createDummyEmail(): array
    {
        $message = new Message(Uuid::uuid4(), 'recipient@test.com', 'FirstName', 'Votre donation !', [], [], 'contact@en-marche.fr', 'En Marche !');
        $message->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        $email = DummyEmailTemplate::createWithMessage($message, 'contact@en-marche.fr');

        return [$message, $email];
    }

    private function getBus(MessageHandlerInterface $handler): MessageBusInterface
    {
        return new MessageBus([new HandleMessageMiddleware(new HandlersLocator([
            AsyncSendMessageCommand::class => [$handler],
        ]))]);
    }
}
