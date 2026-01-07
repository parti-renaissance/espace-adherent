<?php

declare(strict_types=1);

namespace App\Form\NationalEvent;

use App\Entity\NationalEvent\NationalEvent;
use App\NationalEvent\DTO\InscriptionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PackageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var NationalEvent $event */
        $event = $options['event'];

        $builder
            ->add('withDiscount', CheckboxType::class, ['required' => false])
            ->add('packageValues', PackageValuesFormType::class)
        ;

        if ($event->isCampus()) {
            $builder->add('roommateIdentifier', TextType::class, ['required' => false]);
        }

        $builder->add('packageValues', PackageValuesFormType::class, [
            'package_config' => $event->packageConfig,
            'reserved_places' => $options['reserved_places'] ?? [],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => InscriptionRequest::class,
            ])
            ->setDefined(['event', 'reserved_places'])
            ->addAllowedTypes('event', NationalEvent::class)
            ->addAllowedTypes('reserved_places', 'array')
        ;
    }
}
