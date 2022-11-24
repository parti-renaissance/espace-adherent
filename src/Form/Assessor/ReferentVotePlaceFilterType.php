<?php

namespace App\Form\Assessor;

use App\Form\DataTransformer\StringToArrayTransformer;
use App\Form\UnitedNationsCountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ReferentVotePlaceFilterType extends DefaultVotePlaceFilterType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('city', TextType::class, ['required' => false])
            ->add('postalCodes', TextType::class, ['required' => false])
            ->add('country', UnitedNationsCountryType::class, ['required' => false])
            ->get('postalCodes')->addModelTransformer(new StringToArrayTransformer())
        ;
    }
}
