<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Subscription\SubscriptionTypeEnum;

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
