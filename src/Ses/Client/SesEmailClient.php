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
 * (429), a 200 with no MessageId (the send was not really accepted, e.g. an unsigned request when no
 * credentials resolve) and any other failure (5xx, network) propagate as exceptions — never a silent
 * "sent". The caller splits them: a 429 (provably rejected) reopens the row and re-dispatches; an ambiguous
 * failure (5xx / network / empty MessageId, where SES may already have sent) quarantines the row terminally
 * (SendErrored, never re-sent) and rethrows. Client-side rate limiting is intentionally NOT done here: at
 * the target volume (a few thousand per send) the 429-as-retryable path is sufficient.
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

        $emailTags = [];
        if (null !== $email->campaignUuid) {
            $emailTags[] = ['Name' => 'campaign_uuid', 'Value' => $email->campaignUuid];
        }
        if (null !== $email->adherentUuid) {
            $emailTags[] = ['Name' => 'adherent_uuid', 'Value' => $email->adherentUuid];
        }

        $request = new SendEmailRequest([
            'FromEmailAddress' => $this->formatFrom($email),
            'Destination' => ['ToAddresses' => [$email->to]],
            'ReplyToAddresses' => null !== $email->replyTo ? [$email->replyTo] : [],
            'ConfigurationSetName' => $this->sesConfigurationSetName ?: null,
            'EmailTags' => $emailTags ?: null,
            'Content' => [
                'Simple' => $simpleContent,
            ],
        ]);

        try {
            $messageId = (string) $this->client->sendEmail($request)->getMessageId();
        } catch (ClientException $exception) {
            if (429 === $exception->getResponse()->getStatusCode()) {
                throw $exception;
            }

            return SesSendOutcome::rejected($exception->getMessage());
        }

        if ('' === $messageId) {
            throw new \RuntimeException('SES SendEmail returned no MessageId: the send was not accepted.');
        }

        return SesSendOutcome::sent($messageId);
    }

    private function formatFrom(SesEmail $email): string
    {
        if (null === $email->fromName) {
            return $email->fromEmail;
        }

        $name = str_replace(["\r", "\n"], '', $email->fromName);

        return \sprintf('%s <%s>', $name, $email->fromEmail);
    }
}
