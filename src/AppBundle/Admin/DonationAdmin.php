<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Show\ShowMapper;

class DonationAdmin extends AbstractAdmin
{
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('isFinished', 'boolean', [
                'label' => 'Don terminé ?',
            ])
            ->add('isSuccessful', 'boolean', [
                'label' => 'Don réussi ?',
            ])
            ->add('amountInEuros', null, [
                'label' => 'Montant',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
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
            ->add('phone', null, [
                'label' => 'Téléphone',
            ])
            ->add('clientIp', null, [
                'label' => 'IP du client',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('donatedAt', null, [
                'label' => 'Date de don',
            ])
            ->add('payboxResultCode', null, [
                'label' => 'Code de résultat Paybox',
            ])
            ->add('payboxAuthorizationCode', null, [
                'label' => 'Code d\'autorisation Paybox',
            ])
            ->add('payboxPayloadAsJson', null, [
                'label' => 'Payload Paybox',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ]);
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
            ->add('isFinished', 'boolean', [
                'label' => 'Don terminé ?',
            ])
            ->add('isSuccessful', 'boolean', [
                'label' => 'Don réussi ?',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ]);
    }
}
