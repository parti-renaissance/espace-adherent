<?php

declare(strict_types=1);

namespace App\Admin;

use App\Form\Admin\SimpleMDEContent;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ConsultationAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('published', null, [
                'label' => 'Publiée',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('published', null, [
                'label' => 'Publiée',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernière mise à jour',
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

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('content', SimpleMDEContent::class, [
                    'label' => 'Contenu',
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
                ->add('url', UrlType::class, [
                    'label' => 'Lien',
                ])
            ->end()
            ->with('Audience', ['class' => 'col-md-6'])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publication',
                    'required' => false,
                    'help' => 'Cochez cette case pour publier la consultation',
                ])
            ->end()
        ;
    }
}
