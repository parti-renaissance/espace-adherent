<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Fallback;

use App\Entity\AdherentMessage\AdherentMessageInterface;

class MandrillCampaignPayloadBuilder
{
    private const string REPLY_TO = 'contact@parti-renaissance.fr';

    private const string ACCOUNT_URL = 'https://parti.re/mon-compte';

    public function __construct(private readonly string $fromEmail)
    {
    }

    /**
     * @param array<int, array{email: string, firstName: string, lastName: string, gender: ?string, publicId: string}> $recipients
     *
     * @return array{message: array<string, mixed>}
     */
    public function build(AdherentMessageInterface $message, string $renderedHtml, array $recipients): array
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
                'html' => $this->resolveMailchimpSystemTags($renderedHtml, $message),
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

    private function resolveMailchimpSystemTags(string $html, AdherentMessageInterface $message): string
    {
        return strtr($html, [
            '*|MC:SUBJECT|*' => (string) $message->getSubject(),
            '*|UNSUB|*' => self::ACCOUNT_URL,
            '*|UPDATE_PROFILE|*' => self::ACCOUNT_URL,
            '*|ARCHIVE|*' => self::ACCOUNT_URL,
        ]);
    }
}
