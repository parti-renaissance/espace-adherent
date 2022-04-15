<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Subscription\SubscriptionTypeEnum;

class NewsletterSubscriptionGlobalListCalculator extends AbstractNewsletterSubscriptionCalculator
{
    public static function getPriority(): int
    {
        return 10;
    }

    public function getLabel(): string
    {
        return 'Inscrits à la liste globale (total)';
    }

    protected function getSubscriptionCode(): string
    {
        return SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL;
    }
}
