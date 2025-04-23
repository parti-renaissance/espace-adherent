<?php

namespace App\Admin;

use App\Adherent\Unregistration\TypeEnum;
use App\Entity\Adherent;
use App\Entity\Unregistration;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Filter\Model\FilterData;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UnregistrationAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'unregisteredAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 64;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('uuid', null, [
                'label' => 'UUID',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('reasons', null, [
                'label' => 'Raisons',
                'template' => 'admin/adherent/list_reasons.html.twig',
            ])
            ->add('comment', null, [
                'label' => 'Commentaire',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/unregistration/list_type.html.twig',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
                'template' => 'admin/unregistration/list_tags.html.twig',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date d\'inscription',
            ])
            ->add('unregisteredAt', null, [
                'label' => 'Date de désinscription',
            ])
            ->add('excludedBy', null, [
                'label' => 'Exclu(e) par',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $reasonsList = array_merge(Unregistration::REASONS_LIST_ADHERENT, Unregistration::REASONS_LIST_USER);

        $filter
            ->add('reasons', CallbackFilter::class, [
                'label' => 'Raisons',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_combine($reasonsList, $reasonsList),
                    'choice_translation_domain' => 'forms',
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $qb->andWhere($qb->expr()->eq(\sprintf('json_contains(%s.reasons, :reason)', $alias), 1));
                    $qb->setParameter(':reason', \sprintf('"%s"', $value->getValue()));

                    return true;
                },
            ])
            ->add('emailHash', CallbackFilter::class, [
                'label' => 'Email',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, FilterData $value) {
                    if (!$value->hasValue()) {
                        return false;
                    }

                    $uuid = Adherent::createUuid($value->getValue());
                    $qb->andWhere(\sprintf('%s.emailHash = :email_hash', $alias));
                    $qb->setParameter('email_hash', $uuid->toString());

                    return true;
                },
            ])
            ->add('unregisteredAt', DateRangeFilter::class, [
                'label' => 'Date de désinscription',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('excludedBy', null, [
                'label' => 'Exclu(e) par',
                'show_filter' => true,
            ])
            ->add('type', ChoiceFilter::class, [
                'label' => 'Type',
                'show_filter' => true,
                'field_type' => EnumType::class,
                'field_options' => [
                    'class' => TypeEnum::class,
                    'choice_label' => static function (TypeEnum $type): string {
                        return 'unregistration.type.'.$type->value;
                    },
                    'multiple' => true,
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('uuid', null, [
                'label' => 'UUID',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
            ])
            ->add('reasons', null, [
                'label' => 'Raisons',
                'template' => 'admin/adherent/show_reasons.html.twig',
            ])
            ->add('comment', null, [
                'label' => 'Commentaire',
            ])
            ->add('registeredAt', null, [
                'label' => 'Date d\'inscription',
            ])
            ->add('unregisteredAt', null, [
                'label' => 'Date de désinscription',
            ])
            ->add('excludedBy', null, [
                'label' => 'Exclu(e) par',
            ])
        ;
    }
}
