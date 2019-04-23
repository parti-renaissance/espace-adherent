<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Donation;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DonationAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('status', null, [
                'label' => 'Statut du don',
                'template' => 'admin/donation/show_status.html.twig',
            ])
            ->add('amountInEuros', null, [
                'label' => 'Montant',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'filter_emojis' => true,
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
            ])
            ->add('gender', null, [
                'label' => 'Gentilé',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('country', null, [
                'label' => 'Pays',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('address', null, [
                'label' => 'Adresse postale',
            ])
            ->add('clientIp', null, [
                'label' => 'IP du client',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernier update',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add(
                'status',
                'doctrine_orm_choice',
                ['label' => 'Statut'],
                ChoiceType::class,
                [
                    'choices' => [
                        'donation.status.'.Donation::STATUS_WAITING_CONFIRMATION => Donation::STATUS_WAITING_CONFIRMATION,
                        'donation.status.'.Donation::STATUS_SUBSCRIPTION_IN_PROGRESS => Donation::STATUS_SUBSCRIPTION_IN_PROGRESS,
                        'donation.status.'.Donation::STATUS_ERROR => Donation::STATUS_ERROR,
                        'donation.status.'.Donation::STATUS_CANCELED => Donation::STATUS_CANCELED,
                        'donation.status.'.Donation::STATUS_FINISHED => Donation::STATUS_FINISHED,
                    ],
                ]
            )
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('amountInEuros', NumberType::class, [
                'label' => 'Montant',
            ])
            ->add('status', null, [
                'label' => 'Statut du don',
                'template' => 'admin/donation/list_status.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }
}
