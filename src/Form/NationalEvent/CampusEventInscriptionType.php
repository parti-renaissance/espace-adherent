<?php

declare(strict_types=1);

namespace App\Form\NationalEvent;

use App\Form\BirthdateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampusEventInscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('birthdate', BirthdateType::class, [
                'years' => array_combine($years = range(date('Y') - 15, date('Y') - 120), $years),
            ])
            ->add('accessibility', TextareaType::class, ['required' => false])
        ;

        if (false === $options['is_edit']) {
            (new CampusTransportType())->buildForm($builder, $options);
        }
    }

    public function getParent(): string
    {
        return CommonEventInscriptionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['transport_configuration', 'reserved_places'])
            ->addAllowedTypes('transport_configuration', ['array'])
            ->addAllowedTypes('reserved_places', ['array'])
        ;
    }
}
