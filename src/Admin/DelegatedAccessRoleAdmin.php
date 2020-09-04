<?php

namespace App\Admin;

use App\Entity\MyTeam\DelegatedAccessEnum;
use App\Entity\MyTeam\DelegatedAccessGroup;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DelegatedAccessRoleAdmin extends AbstractAdmin
{
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('group', null, [
                'label' => 'Groupe',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => DelegatedAccessEnum::TYPES,
                'choice_label' => function ($choice) {
                    return $choice;
                },
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('group', EntityType::class, [
                'label' => 'Groupe',
                'class' => DelegatedAccessGroup::class,
                'choice_label' => 'name',
                'required' => false,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('type', null, [
                'label' => 'Type',
            ])
            ->add('group', null, [
                'label' => 'Groupe',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    public function configureActionButtons($action, $object = null)
    {
        $actions = parent::configureActionButtons($action, $object);

        $actions['list_groups'] = [
            'template' => 'admin/delegated_access_role/list_groups_action.html.twig',
        ];

        return $actions;
    }
}
