<?php

namespace App\Form\Committee;

use App\Committee\Filter\ListFilter;
use App\Form\ZoneAutoCompleteType;
use App\Validator\ManagedZone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('zones', ZoneAutoCompleteType::class, [
                'remote_params' => [
                    'space_type' => $options['space_type'],
                    'active_only' => true,
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
                'data_class' => ListFilter::class,
                'space_type' => null,
            ])
            ->setAllowedTypes('space_type', 'string')
        ;
    }
}
