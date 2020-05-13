<?php

namespace App\Form;

use App\Intl\UnitedNationsBundle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\Loader\CallbackChoiceLoader;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitedNationsCountryType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'locale' => 'fr',
            'choice_translation_domain' => false,
            'choice_loader' => function (Options $options) {
                // lazy load the choices
                $locale = $options['locale'];

                return new CallbackChoiceLoader(function () use ($locale) {
                    return array_flip(UnitedNationsBundle::getCountries($locale));
                });
            },
        ]);

        $resolver->setAllowedTypes('locale', 'string');
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
