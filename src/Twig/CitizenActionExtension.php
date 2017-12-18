<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CitizenActionExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            // Permissions
            new TwigFunction('can_create_citizen_action', [CitizenActionRuntime::class, 'canCreateCitizenActionFor']),
            new TwigFunction('can_register_citizen_action', [CitizenActionRuntime::class, 'canRegisterOnCitizenAction']),
        ];
    }
}
