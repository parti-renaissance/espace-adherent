<?php

namespace AppBundle\Mailjet;

use AppBundle\Mailjet\Exception\MailjetException;

final class MailjetUtils
{
    public static function getSuccessfulRecipientsFromJson(string $httpJsonResponsePayload, bool $canonicalEmails = false): array
    {
        $list = [];
        if (null === $data = json_decode($httpJsonResponsePayload, true)) {
            throw new MailjetException(sprintf('Unable to decode HTTP response JSON payload: %s', $httpJsonResponsePayload));
        }

        foreach ($data['Sent'] as $recipient) {
            $key = $canonicalEmails ? static::canonicalize($recipient['Email']) : $recipient['Email'];
            $list[$key] = $recipient['Email'];
        }

        return $list;
    }

    public static function canonicalize(string $text): string
    {
        return mb_strtolower($text);
    }
}
