<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Event\BaseEventCategory;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EventCategoryAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'name';
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', null, ['label' => 'Nom'])
            ->add('description', null, ['label' => 'Description'])
            ->add('slug', null, [
                'label' => 'Slug',
                'help' => 'Sera utilisé pour la recherche : https://en-marche.fr/evenements/categorie/[votre-valeur]<br />Doit être unique',
                'help_html' => true,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'Visibilité',
                'choices' => [
                    'Visible' => BaseEventCategory::ENABLED,
                    'Masqué' => BaseEventCategory::DISABLED,
                ],
            ])
            ->add('eventGroupCategory', ModelType::class, ['label' => 'Groupe', 'btn_add' => false])
            ->add('alert', null, ['label' => 'Alerte'])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('status', null, [
                'label' => 'Visibilité',
                'template' => 'admin/event_category/list_status.html.twig',
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
