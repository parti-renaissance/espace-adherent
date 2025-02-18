<?php

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class TipTapContentType extends AbstractType
{
    public function getParent(): string
    {
        return HiddenType::class;
    }
}
