<?php

declare(strict_types=1);

namespace App\Form\Committee;

use App\Committee\Filter\CommitteeDesignationsListFilter;
use App\Form\ZoneAutoCompleteType;
use App\Validator\ManagedZone;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommitteeDesignationsListFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('committeeName', TextType::class, [
                'required' => false,
            ])
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

    public function getBlockPrefix(): string
    {
        return 'f';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => CommitteeDesignationsListFilter::class,
                'method' => Request::METHOD_GET,
                'csrf_protection' => false,
            ])
            ->setRequired('space_type')
            ->setAllowedTypes('space_type', 'string')
        ;
    }
}
