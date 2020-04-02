<?php

namespace AppBundle\Admin\ElectedRepresentative;

use AppBundle\Election\VoteListNuanceEnum;
use AppBundle\Entity\ElectedRepresentative\LaREMSupportEnum;
use AppBundle\Entity\ElectedRepresentative\MandateTypeEnum;
use AppBundle\Form\ElectedRepresentative\ZoneType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MandateAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['create', 'edit', 'delete']);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('number', TextType::class, [
                'disabled' => true,
                'label' => '#',
            ])
            ->add('type', ChoiceType::class, [
                'placeholder' => '--',
                'choices' => MandateTypeEnum::CHOICES,
                'label' => 'Type',
            ])
            ->add('politicalAffiliation', ChoiceType::class, [
                'choices' => VoteListNuanceEnum::toArray(),
                'label' => 'Nuance politique',
                'required' => true,
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
                    return 'elected_representative.mandate.larem_support.'.\mb_strtolower($key);
                },
            ])
            ->add('onGoing', CheckboxType::class, [
                'label' => 'En cours',
                'required' => false,
            ])
            ->add('beginAt', 'sonata_type_date_picker', [
                'label' => 'Date de début de mandat',
            ])
            ->add('finishAt', 'sonata_type_date_picker', [
                'label' => 'Date de fin de mandat',
                'required' => false,
                'error_bubbling' => true,
            ])
            ->add('zone', ZoneType::class, [
                'label' => 'Périmètre géographique',
                'required' => false,
            ])
        ;
    }
}
