<?php

namespace App\Admin\ElectedRepresentative;

use App\Election\VoteListNuanceEnum;
use App\Entity\ElectedRepresentative\LaREMSupportEnum;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
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

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('number', TextType::class, [
                'disabled' => true,
                'label' => '#',
                'attr' => ['class' => 'width-50'],
            ])
            ->add('type', ChoiceType::class, [
                'placeholder' => '--',
                'choices' => MandateTypeEnum::CHOICES,
                'label' => 'Type',
                'attr' => ['class' => 'width-125'],
            ])
            ->add('politicalAffiliation', ChoiceType::class, [
                'choices' => VoteListNuanceEnum::getChoices(),
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
            ->add('geoZone', ModelAutocompleteType::class, [
                'label' => 'Périmètre géographique',
                'property' => ['name', 'code'],
                'required' => false,
                'btn_add' => false,
            ])
        ;
    }
}
