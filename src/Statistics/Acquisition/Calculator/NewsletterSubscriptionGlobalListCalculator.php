<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Subscription\SubscriptionTypeEnum;

class NewsletterSubscriptionGlobalListCalculator extends AbstractNewsletterSubscriptionCalculator
{
    public function getLabel(): string
    {
        return 'Inscrits à la liste globale (total)';
    }

    protected function getSubscriptionCode(): string
    {
        return SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL;
    }
}
