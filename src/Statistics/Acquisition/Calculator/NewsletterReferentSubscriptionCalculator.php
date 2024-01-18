<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Subscription\SubscriptionTypeEnum;

class NewsletterReferentSubscriptionCalculator extends AbstractNewsletterSubscriptionCalculator
{
    public static function getPriority(): int
    {
        return 7;
    }

    public function getLabel(): string
    {
        return 'Adhérents inscrits aux emails de leur référent (total)';
    }

    protected function getSubscriptionCode(): string
    {
        return SubscriptionTypeEnum::REFERENT_EMAIL;
    }
}
