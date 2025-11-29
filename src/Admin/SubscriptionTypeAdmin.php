<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SubscriptionTypeAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'label';
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('label', null, [
                'label' => 'Libélé',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('externalId', null, [
                'label' => 'Id dans le service externe',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isCreation = null === $this->getSubject() || null === $this->getSubject()->getCode();

        $form
            ->add('label', TextType::class, [
                'label' => 'Libélé',
            ])
            ->add('code', null, [
                'label' => 'Code',
                'disabled' => !$isCreation,
                'help' => 'Le code ne devrait contenir que des lettres et underscores. Par exemple, subscribed_emails_committees',
            ])
            ->add('externalId', TextType::class, [
                'required' => false,
                'label' => 'Id dans le service externe',
                'help' => 'Cela peut être, par exemple, un id d\'un liste Mailchimp correspondant à ce type de subscription',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('label', null, [
                'label' => 'Libélé',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('externalId', null, [
                'label' => 'Id dans le service externe',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('delete');
    }
}
