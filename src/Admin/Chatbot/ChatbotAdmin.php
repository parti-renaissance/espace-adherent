<?php

namespace App\Admin\Chatbot;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ChatbotAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Metadonnées 🧱', ['class' => 'col-md-6'])
                ->add('code', TextType::class, [
                    'label' => 'Code',
                ])
                ->add('assistantId', TextType::class, [
                    'label' => 'ID Assistant',
                ])
                ->add('enabled', CheckboxType::class, [
                    'label' => 'Activé',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('code', null, [
                'label' => 'Code',
                'show_filter' => true,
            ])
            ->add('assistantId', null, [
                'label' => 'ID Assistant',
                'show_filter' => true,
            ])
            ->add('enabled', null, [
                'label' => 'Activé ?',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('code', null, [
                'label' => 'Code',
            ])
            ->add('assistantId', null, [
                'label' => 'ID Assistant',
            ])
            ->add('enabled', null, [
                'label' => 'Activé',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }
}
