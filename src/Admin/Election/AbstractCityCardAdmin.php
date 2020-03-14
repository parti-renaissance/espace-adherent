<?php

namespace AppBundle\Admin\Election;

use AppBundle\Entity\Election\CityCard;
use AppBundle\Form\EventListener\CityCardListener;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AbstractCityCardAdmin extends AbstractAdmin
{
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept([
            'list',
            'edit',
            'export',
        ]);
    }

    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = parent::createQuery($context);

        $proxyQuery
            ->innerJoin('o.city', 'city')
            ->innerJoin('city.department', 'department')
            ->innerJoin('department.region', 'region')
            ->addSelect('city, department, region')
        ;

        return $proxyQuery;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->getFormBuilder()
            ->addEventSubscriber(new CityCardListener())
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('city.name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('city.inseeCode', null, [
                'label' => 'Code INSEE',
                'show_filter' => true,
            ])
            ->add('city.postalCodes', null, [
                'label' => 'Code postal',
                'show_filter' => true,
            ])
            ->add('city.department', null, [
                'label' => 'Département',
                'multiple' => true,
                'show_filter' => true,
            ])
            ->add('city.department.region', null, [
                'label' => 'Région',
                'multiple' => true,
                'show_filter' => true,
            ])
            ->add('priority', ChoiceFilter::class, [
                'label' => 'Priorité',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => CityCard::PRIORITY_CHOICES,
                    'choice_label' => function (string $choice) {
                        return "election.city_card.priority.$choice";
                    },
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('city.name', null, [
                'label' => 'Nom',
            ])
            ->add('city.inseeCode', null, [
                'label' => 'Code INSEE',
            ])
            ->add('city.department', null, [
                'label' => 'Département',
            ])
            ->add('city.department.region', null, [
                'label' => 'Région',
            ])
            ->add('priority', null, [
                'label' => 'Priorité',
                'template' => 'admin/election/city_card/_list_priority.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Nom' => 'city.name',
            'Code INSEE' => 'city.inseeCode',
            'Département' => 'city.department',
            'Région' => 'city.department.region',
        ];
    }
}
