<?php

namespace App\Mandrill;

use App\Mailer\EmailClientInterface;
use App\Mailer\EmailTemplateInterface;
use App\Mailer\Exception\MailerException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class EmailClient implements EmailClientInterface
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, string $apiKey)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
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
        $body['key'] = $this->apiKey;

        return $body;
    }

    private function filterResponse(ResponseInterface $response, string $error = 'Mailer error'): string
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new MailerException($error);
        }

        return $response->getContent();
    }
}
