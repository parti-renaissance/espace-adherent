<?php

declare(strict_types=1);

namespace App\Donation;

use App\Entity\Donation;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum DonationGlobalStatus: string implements TranslatableInterface
{
    case PAID = 'paid';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';

    public static function fromDonationStatus(string $status): self
    {
        return match ($status) {
            Donation::STATUS_FINISHED,
            Donation::STATUS_SUBSCRIPTION_IN_PROGRESS => self::PAID,
            Donation::STATUS_REFUNDED => self::REFUNDED,
            default => self::FAILED,
        };
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('donation.status.'.$this->value, locale: $locale);
    }
}
