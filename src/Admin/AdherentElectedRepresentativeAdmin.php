<?php

namespace App\Admin;

use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class AdherentElectedRepresentativeAdmin extends AbstractAdherentAdmin
{
    protected $baseRoutePattern = 'adherents-elus';
    protected $baseRouteName = 'adherents-elus';

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $rootAlias = $query->getRootAliases()[0];

        $query
            ->innerJoin(
                ElectedRepresentativeAdherentMandate::class,
                'er_adherent_mandate',
                Expr\Join::WITH,
                sprintf('%s.id = er_adherent_mandate.adherent', $rootAlias)
            )
            ->andWhere('er_adherent_mandate.finishAt IS NULL')
        ;

        return $query;
    }

    protected function configureListFields(ListMapper $list): void
    {
        parent::configureListFields($list);

        $list
            ->add('mandates', null, [
                'label' => 'Mandats déclarés',
                'template' => 'admin/adherent/list_declared_mandates.html.twig',
            ])
        ;

        $list->reorder([
            'id',
            'lastName',
            'registeredAt',
            'lastMembershipDonation',
            'lastLoggedAt',
            'mandates',
            'allMandates',
            'mailchimpStatus',
            ListMapper::NAME_ACTIONS,
        ]);
    }
}
