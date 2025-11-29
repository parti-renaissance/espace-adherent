<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class RedirectionAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'updatedAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 200;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('from', TextType::class, [
                'label' => 'Rediriger depuis ...',
                'help' => 'Uniquement le chemin (sans le domaine) : pour "https://en-marche.fr/marseille", indiquez "/marseille".',
            ])
            ->add('to', TextType::class, [
                'label' => 'Vers ...',
                'help' => 'Soit un chemin, soit une URL complète.',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de redirection',
                'choices' => [
                    'Permanente (HTTP 301)' => 301,
                    'Temporaire (HTTP 302)' => 302,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('from', null, [
                'label' => 'Rediriger depuis ...',
                'help' => 'Uniquement le chemin (sans le domaine) : pour "https://en-marche.fr/marseille", indiquez "/marseille".',
            ])
            ->add('to', null, [
                'label' => 'Vers ...',
                'help' => 'Soit un chemin, soit une URL complète.',
            ])
            ->add('type', null, [
                'label' => 'Type de redirection',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de mise à jour',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
