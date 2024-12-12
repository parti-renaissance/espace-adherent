<?php

namespace App\Form\AdherentMessage;

use App\Entity\AdherentMessage\Filter\AdherentGeoZoneFilter;
use App\Form\GenderType;
use App\Form\ZoneAutoCompleteType;
use App\Validator\ManagedZone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdherentGeoZoneFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('gender', GenderType::class, [
                'placeholder' => 'Tous',
                'expanded' => true,
                'required' => false,
            ])
            ->add('ageMin', IntegerType::class, ['required' => false, 'attr' => ['min' => 1]])
            ->add('ageMax', IntegerType::class, ['required' => false, 'attr' => ['min' => 1]])
            ->add('firstName', TextType::class, ['required' => false])
            ->add('lastName', TextType::class, ['required' => false])
            ->add('zone', ZoneAutoCompleteType::class, [
                'multiple' => false,
                'remote_params' => [
                    'space_type' => $options['space_type'],
                ],
                'constraints' => [
                    new ManagedZone($options['space_type']),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => AdherentGeoZoneFilter::class,
                'space_type' => null,
            ])
            ->setAllowedTypes('space_type', 'string')
        ;
    }
}
