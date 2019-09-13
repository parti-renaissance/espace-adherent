<?php

namespace AppBundle\Mandrill;

use AppBundle\Mailer\EmailClientInterface;
use AppBundle\Mailer\Exception\MailerException;
use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\Response;

class EmailClient implements EmailClientInterface
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function sendEmail(string $email): string
    {
        $body = json_decode($email, true);
        $body['key'] = $this->client->getConfig('api_key');

        $response = $this->client->request('POST', 'messages/send-template.json', ['json' => $body]);

        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new MailerException('Unable to send email to recipients.');
        }

        return (string) $response->getBody();
    }
}
