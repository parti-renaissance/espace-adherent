<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Mooc\BaseMoocElement;
use App\Entity\Mooc\Mooc;
use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MoocChapterAdmin extends AbstractAdmin
{
    use SortableAdminTrait;

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->addOrderBy('o.mooc', 'ASC')
            ->addOrderBy('o.position', 'ASC')
        ;

        return $query;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('Chapitres')
                ->with('Général', ['class' => 'col-md-6'])
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                    ])
                    ->add('slug', TextType::class, [
                        'label' => 'Slug',
                        'disabled' => true,
                    ])
                    ->add('published', CheckboxType::class, [
                        'label' => 'Publié',
                        'required' => false,
                    ])
                    ->add('publishedAt', DatePickerType::class, [
                        'label' => 'Date de publication',
                    ])
                    ->add('mooc', EntityType::class, [
                        'class' => Mooc::class,
                        'placeholder' => 'Sélectionner un Mooc',
                    ])
                ->end()
        ;
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $form
                ->with('Media', ['class' => 'col-md-6'])
                    ->add('elements', EntityType::class, [
                        'label' => 'Éléments',
                        'multiple' => true,
                        'class' => BaseMoocElement::class,
                        'by_reference' => false,
                    ])
                ->end()
            ;
        }
        $form
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('published', null, [
                'label' => 'Publié',
                'editable' => true,
            ])
            ->add('publishedAt', null, [
                'label' => 'Date de publication',
            ])
            ->add('mooc', null, [
                'label' => 'MOOC associé',
            ])
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'move' => [
                        'template' => '@RunroomSortableBehavior/sort.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
