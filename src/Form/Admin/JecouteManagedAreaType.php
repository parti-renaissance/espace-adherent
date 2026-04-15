<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Controller\Admin\ZoneAutocompleteController;
use App\Entity\JecouteManagedArea;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class JecouteManagedAreaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('zone', AdminZoneAutocompleteType::class, [
                'required' => false,
                'label' => false,
                'preset' => ZoneAutocompleteController::PRESET_JECOUTE_MANAGED_AREA,
            ])
            ->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
                /** @var JecouteManagedArea $data */
                $data = $event->getData();

                if ($data instanceof JecouteManagedArea && !$data->getZone()) {
                    $event->setData(null);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JecouteManagedArea::class,
        ]);
    }
}
