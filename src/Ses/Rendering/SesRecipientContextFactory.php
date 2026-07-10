<?php

declare(strict_types=1);

namespace App\Ses\Rendering;

use App\AdherentMessage\Variable\Dictionary;
use App\Utils\StringCleaner;
use App\ValueObject\Genders;

class SesRecipientContextFactory
{
    public function create(SesRecipient $recipient): array
    {
        return array_map(
            static function (string $value): string {
                return StringCleaner::htmlspecialchars($value);
            },
            $this->buildRaw($recipient)
        );
    }

    public function createForSubject(SesRecipient $recipient): array
    {
        return $this->buildRaw($recipient);
    }

    private function buildRaw(SesRecipient $recipient): array
    {
        return [
            $this->code(Dictionary::SALUTATION) => $this->salutation($recipient->gender, $recipient->firstName),
            $this->code(Dictionary::FIRST_NAME) => $recipient->firstName,
            $this->code(Dictionary::LAST_NAME) => $recipient->lastName,
            $this->code(Dictionary::PUBLIC_ID) => (string) $recipient->publicId,
        ];
    }

    private function salutation(?string $gender, string $firstName): string
    {
        return match ($gender) {
            Genders::FEMALE => 'Chère '.$firstName,
            Genders::MALE => 'Cher '.$firstName,
            default => '',
        };
    }

    private function code(string $name): string
    {
        return \sprintf('{{%s}}', $name);
    }
}
