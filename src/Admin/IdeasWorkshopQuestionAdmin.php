<?php

namespace AppBundle\Admin;

use AppBundle\Form\IdeasWorkshop\QuestionType;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class IdeasWorkshopQuestionAdmin extends AbstractAdmin
{
    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = parent::createQuery($context);

        $proxyQuery
            ->leftJoin('o.guideline', 'g')
            ->addOrderBy('g.position', 'ASC')
            ->addOrderBy('o.position', 'ASC')
        ;

        return $proxyQuery;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('enabled', null, [
                'label' => 'Visibilité',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $builder = $formMapper
            ->getFormBuilder()
            ->getFormFactory()
            ->createBuilder(QuestionType::class)
        ;

        $formMapper
            ->add($builder->get('category'))
            ->add($builder->get('name'))
            ->add('guideline', null, [
                'label' => 'Guideline',
            ])
            ->add($builder->get('position'))
            ->add($builder->get('required'))
            ->add($builder->get('enabled'))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('category', null, [
                'label' => 'Catégorie',
                'show_filter' => true,
            ])
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('guideline.name', null, [
                'label' => 'Nom de la partie',
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('enabled', null, [
                'label' => 'Visibilité',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
