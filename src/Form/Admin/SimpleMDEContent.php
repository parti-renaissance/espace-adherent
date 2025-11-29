<?php

declare(strict_types=1);

namespace App\Form\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SimpleMDEContent extends AbstractType
{
    public function getParent(): string
    {
        return TextareaType::class;
    }
}
