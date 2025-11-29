<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\Geo\Zone;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;

class AdherentElectedRepresentativeAdmin extends AbstractAdherentAdmin
{
    protected $baseRoutePattern = 'adherents-elus';
    protected $baseRouteName = 'adherents-elus';

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        parent::configureQuery($query);

        $rootAlias = $query->getRootAliases()[0];

        $query
            ->innerJoin(
                ElectedRepresentativeAdherentMandate::class,
                'er_adherent_mandate',
                Expr\Join::WITH,
                \sprintf('%s.id = er_adherent_mandate.adherent', $rootAlias)
            )
            ->andWhere('er_adherent_mandate.finishAt IS NULL')
        ;

        return $query;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        parent::configureDatagridFilters($filter);

        $filter
            ->add('adherentMandates', CallbackFilter::class, [
                'label' => 'Périmètres géographiques des mandats',
                'field_type' => ModelAutocompleteType::class,
                'show_filter' => true,
                'field_options' => [
                    'class' => Zone::class,
                    'multiple' => true,
                    'context' => 'form',
                    'property' => ['code', 'name'],
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'req_params' => [
                        'field' => 'zone',
                        '_sonata_admin' => ElectedRepresentativeAdherentMandateAdmin::SERVICE_ID,
                    ],
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $zones = $value->getValue();

                    if ($zones instanceof Collection) {
                        $zones = $zones->toArray();
                    } elseif (!\is_array($zones)) {
                        $zones = [$zones];
                    }

                    $ids = array_map(static function (Zone $zone) {
                        return $zone->getId();
                    }, $zones);

                    $qb
                        ->innerJoin('er_adherent_mandate.zone', 'mandate_zone_filter')
                        ->leftJoin('mandate_zone_filter.parents', 'mandate_zone_parent_filter')
                        ->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->in('mandate_zone_filter.id', $ids),
                                $qb->expr()->in('mandate_zone_parent_filter.id', $ids),
                            )
                        )
                    ;

                    return true;
                },
            ])
        ;
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
            'publicId',
            'lastName',
            'registeredAt',
            'lastMembershipDonation',
            'lastLoggedAt',
            'type',
            'mandates',
            'allMandates',
            ListMapper::NAME_ACTIONS,
        ]);
    }
}
