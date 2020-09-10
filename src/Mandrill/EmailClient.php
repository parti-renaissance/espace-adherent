<?php

namespace App\Mandrill;

use App\Mailer\EmailClientInterface;
use App\Mailer\EmailTemplateInterface;
use App\Mailer\Exception\MailerException;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
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
        $response = $this->client->request('POST', 'messages/send-template.json', ['json' => $this->prepareBody($email)]);

        return $this->filterResponse($response, 'Unable to send email to recipients.');
    }

    public function renderEmail(EmailTemplateInterface $email): string
    {
        $response = $this->client->request('POST', 'templates/render.json', ['json' => $this->prepareBody(json_encode($email))]);

        $data = json_decode($this->filterResponse($response), true);

        return $data['html'] ?? '';
    }

    private function prepareBody(string $email): array
    {
        $body = json_decode($email, true);
        $body['key'] = $this->client->getConfig('api_key');

        return $body;
    }

    private function filterResponse(ResponseInterface $response, string $error = 'Mailer error'): string
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new MailerException($error);
        }

        return (string) $response->getBody();
    }
}
