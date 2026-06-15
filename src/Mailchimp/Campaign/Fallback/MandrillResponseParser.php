<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Fallback;

class MandrillResponseParser
{
    public function parse(string $responseBody): MandrillSendResult
    {
        $decoded = json_decode($responseBody, true);

        if (!\is_array($decoded)) {
            return new MandrillSendResult(0, 0, 0, 0);
        }

        $sent = $queued = $rejected = $invalid = 0;
        $rejectedEmails = [];

        foreach ($decoded as $entry) {
            if (!\is_array($entry)) {
                continue;
            }

            $email = (string) ($entry['email'] ?? '');

            switch ($entry['status'] ?? null) {
                case 'sent':
                    ++$sent;
                    break;
                case 'queued':
                case 'scheduled':
                    ++$queued;
                    break;
                case 'rejected':
                    ++$rejected;
                    $rejectedEmails[] = $email;
                    break;
                case 'invalid':
                    ++$invalid;
                    $rejectedEmails[] = $email;
                    break;
            }
        }

        return new MandrillSendResult($sent, $queued, $rejected, $invalid, $rejectedEmails);
    }
}
