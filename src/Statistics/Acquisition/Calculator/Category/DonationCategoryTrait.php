<?php

namespace AppBundle\Statistics\Acquisition\Calculator\Category;

trait DonationCategoryTrait
{
    public function getCategory(): string
    {
        return 'Dons';
    }
}
