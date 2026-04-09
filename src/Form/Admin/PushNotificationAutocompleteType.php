<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Admin\PushNotificationAdmin;
use App\Entity\PushNotification;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PushNotificationAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => PushNotification::class,
            'property' => ['title'],
            'minimum_input_length' => 0,
            'items_per_page' => 10,
            'route' => ['name' => PushNotificationAdmin::AUTOCOMPLETE_ROUTE],
        ]);
    }

    public function getParent(): string
    {
        return ModelAutocompleteType::class;
    }
}
