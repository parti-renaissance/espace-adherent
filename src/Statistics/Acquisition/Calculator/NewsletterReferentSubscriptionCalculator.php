<?php

namespace App\Statistics\Acquisition\Calculator;

use App\Subscription\SubscriptionTypeEnum;

class NewsletterReferentSubscriptionCalculator extends AbstractNewsletterSubscriptionCalculator
{
    public function getLabel(): string
    {
        return 'Adhérents inscrits aux mails de leur référent (total)';
    }

    protected function getSubscriptionCode(): string
    {
        return SubscriptionTypeEnum::REFERENT_EMAIL;
    }
}
