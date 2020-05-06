<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SubscriptionTypeAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'label',
    ];

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
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

    protected function configureFormFields(FormMapper $formMapper)
    {
        $isCreation = null === $this->getSubject() || null === $this->getSubject()->getCode();

        $formMapper
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

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('label', null, [
                'label' => 'Libélé',
            ])
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('externalId', null, [
                'label' => 'Id dans le service externe',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('delete');
    }
}
