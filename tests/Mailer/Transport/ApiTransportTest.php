<?php

namespace Tests\App\Mailer\Transport;

use App\Mailer\Exception\MailerException;
use App\Mailer\Transport\ApiTransport;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tests\App\Test\Mailer\DummyEmailClient;
use Tests\App\Test\Mailer\DummyEmailTemplate;

class ApiTransportTest extends TestCase
{
    public function testCannotSendTemplateEmail()
    {
        $this->expectException(MailerException::class);
        $this->expectExceptionMessage('Unable to send email to recipients.');
        $httpClient = $this->getMockBuilder(HttpClientInterface::class)->getMock();
        $httpClient->expects($this->once())->method('request')->willReturn(new MockResponse('', ['http_code' => 400]));

        $client = new DummyEmailClient($httpClient);
        $transport = new ApiTransport($client);
        $transport->sendTemplateEmail($this->createDummyEmail());
    }

    public function testSendTemplateEmail()
    {
        $email = $this->createDummyEmail();

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
        $transport = new ApiTransport($client);
        $transport->sendTemplateEmail($email);

        $this->assertSame($body, $email->getHttpResponsePayload());
    }

    private function createDummyEmail(): DummyEmailTemplate
    {
        $email = new DummyEmailTemplate(Uuid::uuid4(), '12345', 'Votre donation !', 'contact@en-marche.fr', 'En Marche !');
        $email->addRecipient('john.smith@example.tld', 'John Smith', ['name' => 'John Smith']);

        return $email;
    }
}
