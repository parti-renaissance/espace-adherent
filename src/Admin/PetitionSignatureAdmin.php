<?php

declare(strict_types=1);

namespace App\Admin;

use App\Admin\Filter\UtmFilter;
use App\Form\CivilityType;
use App\Query\Utils\MultiColumnsSearchHelper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PetitionSignatureAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('delete')
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('petitionName', null, ['label' => 'Pétition', 'disabled' => true])
                ->add('civility', CivilityType::class, ['label' => 'Civilité'])
                ->add('firstName', null, ['label' => 'Prénom'])
                ->add('lastName', null, ['label' => 'Nom'])
                ->add('emailAddress', null, ['label' => 'Email'])
                ->add('postalCode', null, ['label' => 'Code postal'])
                ->add('phone', null, ['label' => 'Tél'])
            ->end()
            ->with('Autre', ['class' => 'col-md-6'])
                ->add('createdAt', null, ['label' => 'Créée le', 'widget' => 'single_text', 'disabled' => true])
                ->add('validatedAt', null, ['label' => 'Email confirmé', 'widget' => 'single_text', 'disabled' => true])
                ->add('newsletter', null, ['label' => 'Accepte recevoir la communication', 'disabled' => true])
                ->add('utmSource', null, ['label' => 'UTM Source'])
                ->add('utmCampaign', null, ['label' => 'UTM Campagne'])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('petitionName', null, ['label' => 'Pétition'])
            ->add('lastName', null, [
                'label' => 'Prénom Nom',
                'template' => 'admin/CRUD/list_identity.html.twig',
            ])
            ->add('postalCode', null, ['label' => 'Code postal'])
            ->add('validatedAt', 'boolean', ['label' => 'Email confirmé'])
            ->add('newsletter', 'boolean', ['label' => 'Newsletter'])
            ->add('utm', null, [
                'label' => 'UTM',
                'virtual_field' => true,
                'template' => 'admin/CRUD/list/utm_list.html.twig',
            ])
            ->add('createdAt', null, ['label' => 'Créée le'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'link' => ['template' => 'admin/petition/list__action_link.html.twig'],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('petitionName', null, ['label' => 'Pétition', 'show_filter' => true])
            ->add('search', CallbackFilter::class, [
                'label' => 'Recherche',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    MultiColumnsSearchHelper::updateQueryBuilderForMultiColumnsSearch(
                        $qb->getQueryBuilder(),
                        $value->getValue(),
                        [
                            ["$alias.firstName", "$alias.lastName"],
                            ["$alias.lastName", "$alias.firstName"],
                            ["$alias.emailAddress", "$alias.emailAddress"],
                        ],
                        [
                            "$alias.phone",
                        ],
                        [
                            "$alias.id",
                            "$alias.uuid",
                        ]
                    );

                    return true;
                },
            ])
            ->add('postalCode', null, ['label' => 'Code postal', 'show_filter' => true])
            ->add('validatedAt', CallbackFilter::class, [
                'label' => 'Email confirmé',
                'show_filter' => true,
                'field_type' => BooleanType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    if ($value->getValue()) {
                        $qb->andWhere("$alias.validatedAt IS NOT NULL");
                    } else {
                        $qb->andWhere("$alias.validatedAt IS NULL");
                    }

                    return true;
                },
            ])
            ->add('utm', UtmFilter::class, ['label' => 'UTM Source / Campagne', 'show_filter' => true])
        ;
    }
}
