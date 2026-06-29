<?php

declare(strict_types=1);

namespace App\Ses\Client;

use AsyncAws\Core\Exception\Http\ClientException;
use AsyncAws\Ses\Input\SendEmailRequest;
use AsyncAws\Ses\SesClient;

/**
 * Thin wrapper around the SES v2 SendEmail operation: sends one fully-rendered HTML email to one
 * recipient and classifies the result.
 *
 * Error policy (see SesSendOutcome): a permanent 4xx rejection becomes a returned outcome; throttling
 * (429) and any other failure (5xx, network) propagate as exceptions so the caller reopens the row and
 * lets Messenger retry. Client-side rate limiting is intentionally NOT done here: at the target volume
 * (a few thousand per send) the 429-as-retryable path is sufficient throttling protection.
 */
class SesEmailClient
{
    public function __construct(
        private readonly SesClient $client,
        private readonly ?string $sesConfigurationSetName = null,
    ) {
    }

    public function sendEmail(SesEmail $email): SesSendOutcome
    {
        $simpleContent = [
            'Subject' => ['Data' => $email->subject, 'Charset' => 'UTF-8'],
            'Body' => [
                'Html' => ['Data' => $email->html, 'Charset' => 'UTF-8'],
            ],
        ];

        if (null !== $email->listUnsubscribeUrl) {
            // RFC 8058 one-click unsubscribe: the provider POSTs to the URL with this body.
            $simpleContent['Headers'] = [
                ['Name' => 'List-Unsubscribe', 'Value' => '<'.$email->listUnsubscribeUrl.'>'],
                ['Name' => 'List-Unsubscribe-Post', 'Value' => 'List-Unsubscribe=One-Click'],
            ];
        }

        $request = new SendEmailRequest([
            'FromEmailAddress' => $this->formatFrom($email),
            'Destination' => ['ToAddresses' => [$email->to]],
            'ReplyToAddresses' => null !== $email->replyTo ? [$email->replyTo] : [],
            'ConfigurationSetName' => $this->sesConfigurationSetName ?: null,
            'Content' => [
                'Simple' => $simpleContent,
            ],
        ]);

        try {
            // getMessageId() resolves the lazy result, raising a ClientException on a 4xx response.
            return SesSendOutcome::sent((string) $this->client->sendEmail($request)->getMessageId());
        } catch (ClientException $exception) {
            if (429 === $exception->getResponse()->getStatusCode()) {
                throw $exception; // throttling: retryable, let Messenger retry
            }

            return SesSendOutcome::rejected($exception->getMessage());
        }
    }

    private function formatFrom(SesEmail $email): string
    {
        if (null === $email->fromName) {
            return $email->fromEmail;
        }

        // Defence in depth: strip CR/LF from the display name so it can never inject anything,
        // even though the structured SESv2 JSON API already builds the headers server-side.
        $name = str_replace(["\r", "\n"], '', $email->fromName);

        return \sprintf('%s <%s>', $name, $email->fromEmail);
    }
}
