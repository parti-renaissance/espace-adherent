<?php

declare(strict_types=1);

namespace App\AdherentMessage\Variable\Renderer;

use App\AdherentMessage\Variable\Dictionary;

class MailchimpVariableRenderer implements PublicationVariableRendererInterface
{
    private const VALUES = [
        Dictionary::SALUTATION => '*|IF:GENDER=female|*Chère*|ELSEIF:GENDER=male|*Cher*|END:IF|* *|FNAME|*',
        Dictionary::FIRST_NAME => '*|FNAME|*',
        Dictionary::LAST_NAME => '*|LNAME|*',
        Dictionary::PUBLIC_ID => '*|PUBLIC_ID|*',
    ];

    public static function getFormat(): string
    {
        return 'mailchimp';
    }

    public function isContextRequired(): bool
    {
        return false;
    }

    public function render(string $content, array $variables, array $context = []): string
    {
        return strtr($content, $this->getReplaceValues($variables));
    }

    private function getReplaceValues(array $variables): array
    {
        $values = [];
        foreach ($variables as $variable) {
            $values[$variable] = self::VALUES[trim($variable, '{}')] ?? $variable;
        }

        return $values;
    }
}
