<?php

declare(strict_types=1);

namespace App\Form\Committee;

use App\Committee\Filter\CommitteeListFilter;
use App\Form\ZoneAutoCompleteType;
use App\Validator\ManagedZone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
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
                'data_class' => CommitteeListFilter::class,
                'space_type' => null,
                'method' => Request::METHOD_GET,
                'csrf_protection' => false,
            ])
            ->setAllowedTypes('space_type', 'string')
        ;
    }
}
