<?php

namespace App\NationalEvent;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum PaymentStatusEnum: string implements TranslatableInterface
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case ERROR = 'error';

    public static function all(): array
    {
        return array_values(self::cases());
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('national_event.payment.status.'.$this->value, locale: $locale);
    }
}
