<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ReCheckboxType extends AbstractType
{
    public function getParent()
    {
        return CheckboxType::class;
    }
}
