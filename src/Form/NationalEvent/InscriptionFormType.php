<?php

declare(strict_types=1);

namespace App\Form\NationalEvent;

use App\Entity\NationalEvent\NationalEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InscriptionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var NationalEvent $event */
        $event = $options['event'];

        if ($event->isPackageEventType()) {
            new PackageFormType()->buildForm($builder, $options);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['reserved_places'])
            ->addAllowedTypes('reserved_places', 'array')
        ;
    }

    public function getParent(): string
    {
        return UserDataFormType::class;
    }
}
