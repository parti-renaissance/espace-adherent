<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Subscription\SubscriptionTypeEnum;

class NewsletterAdherentSubscriptionFridayListCalculator extends AbstractNewsletterSubscriptionCalculator
{
    public static function getPriority(): int
    {
        return 8;
    }

    public function getLabel(): string
    {
        return 'Adhérents inscrits à la lettre du vendredi (total)';
    }

    protected function getSubscriptionCode(): string
    {
        return SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL;
    }
}
