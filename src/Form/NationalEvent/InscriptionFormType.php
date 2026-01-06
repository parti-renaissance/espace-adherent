<?php

declare(strict_types=1);

namespace App\Form\NationalEvent;

use App\Entity\NationalEvent\NationalEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

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

    public function getParent(): string
    {
        return UserDataFormType::class;
    }
}
