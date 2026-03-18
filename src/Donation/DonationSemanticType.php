<?php

declare(strict_types=1);

namespace App\Donation;

use App\Entity\Donation;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum DonationSemanticType: string implements TranslatableInterface
{
    case SIMPLE = 'simple';
    case RECURRING = 'recurring';
    case MEMBERSHIP = 'membership';

    public static function fromDonation(Donation $donation): self
    {
        if ($donation->isMembership()) {
            return self::MEMBERSHIP;
        }

        if ($donation->isSubscription()) {
            return self::RECURRING;
        }

        return self::SIMPLE;
    }

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('donation.type.'.$this->value, locale: $locale);
    }
}
