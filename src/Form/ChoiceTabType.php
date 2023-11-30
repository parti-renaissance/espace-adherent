<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ChoiceTabType extends AbstractType
{
    public function getParent()
    {
        return ChoiceType::class;
    }
}
