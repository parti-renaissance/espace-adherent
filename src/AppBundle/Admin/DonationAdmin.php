<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\BooleanType;

class DonationAdmin extends AbstractAdmin
{
    protected function configureShowFields(ShowMapper $show)
    {
        $show
            ->add('isFinished', null, [
                'label' => 'Don terminé ?',
            ])
            ->add('amount', null, [
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
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('donatedAt', null, [
                'label' => 'Date de don',
            ])
            ->add('payboxResultCode', null, [
                'label' => 'Code de résultat Paybox',
            ])
            ->add('payboxAuthorization', null, [
                'label' => 'Code d\'autorisation Paybox',
            ])
            ->add('clientIp', null, [
                'label' => 'IP du client',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('amount', null, [
                'label' => 'Montant',
            ])
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
            ->add('email', null, [
                'label' => 'Adresse e-mail',
            ])
            ->add('amount', NumberType::class, [
                'label' => 'Montant',
            ])
            ->add('isFinished', BooleanType::class, [
                'label' => 'Don terminé ?',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                ],
            ]);
    }
}
