<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReChoiceTabType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }
}
