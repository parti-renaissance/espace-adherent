<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

use App\AdherentMessage\Variable\Dictionary;
use App\Utils\StringCleaner;
use App\ValueObject\Genders;

/**
 * Builds the per-recipient substitution context: each Dictionary code mapped to its real value.
 *
 * Values are HTML-escaped here (anti-injection, red-team #15). The salutation reproduces the
 * Mailchimp behaviour (red-team #14): "Chère"/"Cher" by gender, but EMPTY when the gender is
 * absent/other — Dictionary::getConfig() would have returned "Cher" in that case, which would be a
 * silent product change.
 */
class SesRecipientContextFactory
{
    public function create(SesRecipient $recipient): array
    {
        return [
            $this->code(Dictionary::SALUTATION) => $this->salutation($recipient->gender, $recipient->firstName),
            $this->code(Dictionary::FIRST_NAME) => StringCleaner::htmlspecialchars($recipient->firstName),
            $this->code(Dictionary::LAST_NAME) => StringCleaner::htmlspecialchars($recipient->lastName),
            $this->code(Dictionary::PUBLIC_ID) => StringCleaner::htmlspecialchars((string) $recipient->publicId),
        ];
    }

    private function salutation(?string $gender, string $firstName): string
    {
        $escapedFirstName = StringCleaner::htmlspecialchars($firstName);

        return match ($gender) {
            Genders::FEMALE => 'Chère '.$escapedFirstName,
            Genders::MALE => 'Cher '.$escapedFirstName,
            default => '',
        };
    }

    private function code(string $name): string
    {
        return \sprintf('{{%s}}', $name);
    }
}
