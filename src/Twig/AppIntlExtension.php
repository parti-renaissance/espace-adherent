<?php

namespace AppBundle\Twig;

use AppBundle\Intl\UnitedNationsBundle;

class AppIntlExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('list_united_nations', [$this, 'getUnitedNationsList'], [
                'needs_context' => true,
            ]),
        ];
    }

    public function getUnitedNationsList($context)
    {
        return UnitedNationsBundle::getCountries($context['app']->getRequest()->getLocale());
    }
}
