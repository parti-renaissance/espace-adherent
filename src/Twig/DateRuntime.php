<?php

namespace App\Twig;

use Twig\Extension\RuntimeExtensionInterface;

class DateRuntime implements RuntimeExtensionInterface
{
    public static function birthDateToAge(\DateTime $birthdate): int
    {
        return (new \DateTime())->diff($birthdate)->y;
    }
}
