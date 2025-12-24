<?php

declare(strict_types=1);

namespace App\NationalEvent;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum NationalEventTypeEnum: string implements TranslatableInterface
{
    case DEFAULT = 'default';
    case CAMPUS = 'campus';
    case JEM = 'jem';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('national_event.type.'.$this->value, locale: $locale);
    }

    public static function all(): array
    {
        return array_values(self::cases());
    }
}
