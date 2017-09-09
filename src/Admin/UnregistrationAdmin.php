<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Adherent;
use Sonata\AdminBundle\{
    Admin\AbstractAdmin, Datagrid\DatagridMapper, Datagrid\ListMapper, Show\ShowMapper
};
use Sonata\DoctrineORMAdminBundle\{
    Datagrid\ProxyQuery, Filter\CallbackFilter
};
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, TextType};

class UnregistrationAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 64,
        '_sort_order' => 'DESC',
        '_sort_by' => 'unregisteredAt',
    ];

    private $_unregistrationReasons;

    public function __construct($code, $class, $baseControllerName, array $unregistrationReasons)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->_unregistrationReasons = $unregistrationReasons;
    }

    public function getTemplate($name)
    {
        if ('list' === $name) {
            return 'admin/adherent/unregistration_list.html.twig';
        }

        return parent::getTemplate($name);
    }

    protected function configureListFields(ListMapper $listMapper)
        : void
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
            ->add('registeredAt', null, [
                'label' => 'Date d\'inscription',
            ])
            ->add('unregisteredAt', null, [
                'label' => 'Date de désinscription',
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
        : void
    {
        $datagridMapper
            ->add('reasons', CallbackFilter::class, [
                'label' => 'Raisons',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip($this->_unregistrationReasons),
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
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
        : void
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
        ;
    }
}
