<?php

namespace AppBundle\Statistics\Acquisition\Calculator\Category;

trait NewsletterSubscriptionCategoryTrait
{
    public function getCategory(): string
    {
        return 'Inscriptions e-mails';
    }
}
