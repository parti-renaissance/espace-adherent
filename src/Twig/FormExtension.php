<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FormExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('delete_form', [FormRuntime::class, 'createDeleteForm'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }
}
