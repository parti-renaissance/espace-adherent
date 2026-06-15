<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Fallback;

use App\AdherentMessage\Variable\Renderer;
use App\Entity\AdherentMessage\AdherentMessageInterface;

class MandrillCampaignPayloadBuilder
{
    private const string REPLY_TO = 'contact@parti-renaissance.fr';

    public function __construct(
        private readonly Renderer $variableRenderer,
        private readonly string $fromEmail,
    ) {
    }

    /**
     * @param array<int, array{email: string, firstName: string, lastName: string, gender: ?string, publicId: string}> $recipients
     *
     * @return array{message: array<string, mixed>}
     */
    public function build(AdherentMessageInterface $message, array $recipients): array
    {
        $to = [];
        $mergeVars = [];

        foreach ($recipients as $recipient) {
            $to[] = [
                'email' => $recipient['email'],
                'name' => trim($recipient['firstName'].' '.$recipient['lastName']),
                'type' => 'to',
            ];

            $mergeVars[] = [
                'rcpt' => $recipient['email'],
                'vars' => [
                    ['name' => 'FNAME', 'content' => $recipient['firstName']],
                    ['name' => 'LNAME', 'content' => $recipient['lastName']],
                    ['name' => 'GENDER', 'content' => $recipient['gender'] ?? ''],
                    ['name' => 'PUBLIC_ID', 'content' => $recipient['publicId']],
                ],
            ];
        }

        return [
            'message' => [
                'html' => $this->variableRenderer->renderMailchimp((string) $message->getContent()),
                'subject' => $message->getSubject(),
                'from_email' => $this->fromEmail,
                'from_name' => $message->getFromName(),
                'headers' => ['Reply-To' => self::REPLY_TO],
                'merge_language' => 'mailchimp',
                'to' => $to,
                'merge_vars' => $mergeVars,
            ],
        ];
    }
}
