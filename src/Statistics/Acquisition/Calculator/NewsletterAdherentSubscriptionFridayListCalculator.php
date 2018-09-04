<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Subscription\SubscriptionTypeEnum;

class NewsletterAdherentSubscriptionFridayListCalculator extends AbstractNewsletterSubscriptionCalculator
{
    public function getLabel(): string
    {
        return 'Adhérents inscrits à la lettre du vendredi (total)';
    }

    protected function getSubscriptionCode(): string
    {
        return SubscriptionTypeEnum::WEEKLY_LETTER_EMAIL;
    }
}
