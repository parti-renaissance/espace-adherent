<?php

declare(strict_types=1);

namespace App\AdherentMessage\Variable\Renderer;

class TipTapVariableRenderer implements PublicationVariableRendererInterface
{
    public static function getFormat(): string
    {
        return 'tiptap';
    }

    public function isContextRequired(): bool
    {
        return true;
    }

    public function render(string $content, array $variables, array $context = []): string
    {
        if (empty($context)) {
            return $content;
        }

        $replacements = [];

        foreach ($context as $variable => $value) {
            $jsonEncodedValue = json_encode((string) $value, \JSON_UNESCAPED_UNICODE);

            $safeValue = trim($jsonEncodedValue, '"');

            $safeValueEscaped = str_replace('"', '\"', $safeValue);

            $searchStandard = \sprintf('"code":"%s"', $variable);
            $replaceStandard = \sprintf('"code":"%s","value":"%s"', $variable, $safeValue);
            $replacements[$searchStandard] = $replaceStandard;

            $searchEscaped = \sprintf('\"code\":\"%s\"', $variable);
            $replaceEscaped = \sprintf('\"code\":\"%s\",\"value\":\"%s\"', $variable, $safeValueEscaped);
            $replacements[$searchEscaped] = $replaceEscaped;
        }

        return strtr($content, $replacements);
    }
}
