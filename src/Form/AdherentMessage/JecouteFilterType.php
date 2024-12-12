<?php

namespace App\Form\AdherentMessage;

use App\Entity\AdherentMessage\Filter\JecouteFilter;
use App\Form\ZoneAutoCompleteType;
use App\Validator\ManagedZone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JecouteFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('postalCode', TextType::class, ['required' => false])
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
                'data_class' => JecouteFilter::class,
                'space_type' => null,
            ])
            ->setAllowedTypes('space_type', 'string')
        ;
    }
}
