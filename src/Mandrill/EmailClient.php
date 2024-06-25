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
    private string $actifKey;

    public function __construct(
        private readonly HttpClientInterface $mandrillClient,
        private readonly string $apiKey,
        private readonly string $appEnvironment,
        string $testApiKey,
    ) {
        if ('production' === $this->appEnvironment) {
            $this->actifKey = $apiKey;
        } else {
            $this->actifKey = $testApiKey;
        }
    }

    public function sendEmail(string $email, bool $resend = false): string
    {
        $response = $this->mandrillClient->request('POST', 'messages/send-template.json', ['json' => $this->prepareBody($email, $resend)]);

        return $this->filterResponse($response);
    }

    public function renderEmail(EmailTemplateInterface $email): string
    {
        $response = $this->mandrillClient->request('POST', 'templates/render.json', ['json' => $this->prepareBody(json_encode($email))]);

        $data = json_decode($this->filterResponse($response), true);

        return $data['html'] ?? '';
    }

    private function prepareBody(string $email, bool $resend = false): array
    {
        $body = json_decode($email, true);
        $body['key'] = $resend ? $this->apiKey : $this->actifKey;

        return $body;
    }

    private function filterResponse(ResponseInterface $response): string
    {
        if (Response::HTTP_OK !== $response->getStatusCode()) {
            throw new MailerException($response->getContent(false));
        }

        return $response->getContent();
    }
}
