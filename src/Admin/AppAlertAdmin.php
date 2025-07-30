<?php

namespace App\Admin;

use App\Form\DateTimePickerType;
use App\Form\JsonType;
use App\JeMengage\Alert\AlertTypeEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class AppAlertAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'beginAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('type', null, ['label' => 'Type'])
            ->add('label', null, ['label' => 'Label'])
            ->add('title', null, ['label' => 'Titre'])
            ->add('isActive', null, ['label' => 'Active'])
            ->add('beginAt', null, ['label' => 'Date de début'])
            ->add('endAt', null, ['label' => 'Date de fin'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['edit' => []]])
            ->add('createdAt', null, ['label' => 'Date de création'])
            ->add('updatedAt', null, ['label' => 'Date de création'])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('type', EnumType::class, ['label' => 'Type', 'class' => AlertTypeEnum::class])
                ->add('label', null, ['label' => 'Label'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('description', TextareaType::class, ['label' => 'Description', 'attr' => ['rows' => 10]])
                ->add('ctaLabel', null, ['label' => 'Label du CTA', 'required' => false])
                ->add('ctaUrl', null, ['label' => 'URL du CTA', 'required' => false])
                ->add('withMagicLink', null, ['label' => 'Avec lien magique', 'required' => false])
                ->add('isActive', null, ['label' => 'Active', 'required' => false])
            ->end()
            ->with('Dates', ['class' => 'col-md-6'])
                ->add('beginAt', DateTimePickerType::class, ['label' => 'Date de début'])
                ->add('endAt', DateTimePickerType::class, ['label' => 'Date de fin'])
            ->end()
            ->with('Images', ['class' => 'col-md-6'])
                ->add('imageUrl', UrlType::class, ['label' => 'URL de l\'image', 'required' => false])
                ->add('shareUrl', UrlType::class, ['label' => 'URL de partage', 'required' => false])
            ->end()
            ->with('Autres', ['class' => 'col-md-6'])
                ->add('data', JsonType::class, [
                    'label' => 'Données',
                    'required' => false,
                    'attr' => ['rows' => 10],
                ])
            ->end()
        ;
    }
}
