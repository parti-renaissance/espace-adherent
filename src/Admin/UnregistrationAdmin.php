<?php

namespace App\Admin;

use App\Entity\Adherent;
use App\Entity\Unregistration;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UnregistrationAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 64,
        '_sort_order' => 'DESC',
        '_sort_by' => 'unregisteredAt',
    ];

    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
    }

    public function getTemplate($name)
    {
        if ('list' === $name) {
            return 'admin/adherent/unregistration_list.html.twig';
        }

        return parent::getTemplate($name);
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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
            ->add('adherent', null, [
                'label' => 'Type',
                'template' => 'admin/unregistration/user_type.html.twig',
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
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $reasonsList = array_merge(Unregistration::REASONS_LIST_ADHERENT, Unregistration::REASONS_LIST_USER);

        $datagridMapper
            ->add('reasons', CallbackFilter::class, [
                'label' => 'Raisons',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_combine($reasonsList, $reasonsList),
                    'choice_translation_domain' => 'forms',
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere($qb->expr()->eq(sprintf('json_contains(%s.reasons, :reason)', $alias), 1));
                    $qb->setParameter(':reason', sprintf('"%s"', $value['value']));

                    return true;
                },
            ])
            ->add('uuid', CallbackFilter::class, [
                'label' => 'E-mail',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $uuid = Adherent::createUuid($value['value']);
                    $qb->andWhere(sprintf('%s.uuid = :uuid', $alias));
                    $qb->setParameter('uuid', $uuid->toString());

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
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('uuid', null, [
                'label' => 'UUID',
            ])
            ->add('postalCode', null, [
                'label' => 'Code postal',
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
