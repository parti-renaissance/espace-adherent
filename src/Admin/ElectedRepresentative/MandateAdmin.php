<?php

declare(strict_types=1);

namespace App\Admin\ElectedRepresentative;

use App\Entity\ElectedRepresentative\LaREMSupportEnum;
use App\Entity\Geo\Zone;
use App\Form\AdherentMandateType;
use App\Form\Admin\AdminZoneAutocompleteType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MandateAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['create', 'edit', 'delete']);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('number', TextType::class, [
                'disabled' => true,
                'label' => '#',
                'attr' => ['class' => 'width-50'],
            ])
            ->add('type', AdherentMandateType::class, [
                'placeholder' => '--',
                'label' => 'Type',
                'attr' => ['class' => 'width-125'],
            ])
            ->add('isElected', ChoiceType::class, [
                'required' => true,
                'label' => 'Élu(e)',
                'choices' => [
                    'global.yes' => true,
                    'global.no' => false,
                ],
            ])
            ->add('laREMSupport', ChoiceType::class, [
                'required' => false,
                'label' => 'Soutien LaREM',
                'placeholder' => '--',
                'choices' => LaREMSupportEnum::toArray(),
                'choice_label' => function ($choice, $key) {
                    return 'elected_representative.mandate.larem_support.'.mb_strtolower($key);
                },
                'attr' => ['class' => 'width-135'],
            ])
            ->add('onGoing', CheckboxType::class, [
                'label' => 'En cours',
                'required' => false,
            ])
            ->add('beginAt', DatePickerType::class, [
                'label' => 'Date de début de mandat',
                'attr' => ['class' => 'width-140'],
            ])
            ->add('finishAt', DatePickerType::class, [
                'label' => 'Date de fin de mandat',
                'required' => false,
                'error_bubbling' => true,
                'attr' => ['class' => 'width-140'],
            ])
            ->add('geoZone', AdminZoneAutocompleteType::class, [
                'label' => 'Périmètre géographique',
                'required' => false,
                'btn_add' => false,
                'zone_types' => [
                    Zone::CUSTOM,
                    Zone::COUNTRY,
                    Zone::REGION,
                    Zone::DEPARTMENT,
                    Zone::DISTRICT,
                    Zone::CITY,
                    Zone::BOROUGH,
                    Zone::CITY_COMMUNITY,
                    Zone::CANTON,
                    Zone::FOREIGN_DISTRICT,
                    Zone::CONSULAR_DISTRICT,
                ],
            ])
        ;
    }
}
