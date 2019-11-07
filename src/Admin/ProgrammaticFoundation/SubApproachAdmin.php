<?php

namespace AppBundle\Admin\ProgrammaticFoundation;

use AppBundle\Entity\ProgrammaticFoundation\Approach;
use AppBundle\Form\PurifiedTextareaType;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class SubApproachAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
    ];

    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = parent::createQuery($context);
        $proxyQuery->addOrderBy('o.approach', 'ASC');
        $proxyQuery->addOrderBy('o.position', 'ASC');

        return $proxyQuery;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('approach', null, [
                'label' => 'Grand axe associé',
                'sortable' => true,
                'sort_parent_association_mappings' => [['fieldName' => 'approach']],
                'sort_field_mapping' => ['fieldName' => 'title'],
            ])
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('subtitle', null, [
                'label' => 'Sous-titre',
            ])
            ->add('isExpanded', null, [
                'label' => 'Ouvert par défaut',
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('approach', null, [
                'label' => 'Grand axe associé',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Axe secondaire')
                ->add('approach', EntityType::class, [
                    'label' => 'Grand axe associé',
                    'class' => Approach::class,
                    'placeholder' => 'Sélectionner un grand axe',
                ])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('subtitle', TextType::class, [
                    'label' => 'Sous-titre',
                ])
                ->add('content', PurifiedTextareaType::class, [
                    'label' => 'Contenu',
                    'attr' => ['class' => 'ck-editor-advanced'],
                    'purifier_type' => 'enrich_content',
                    'filter_emojis' => true,
                    'required' => false,
                ])
                ->add('isExpanded', BooleanType::class, [
                    'label' => 'Ouvert par défaut',
                ])
                ->add('position', IntegerType::class, [
                    'label' => 'Ordre d\'affichage',
                    'scale' => 0,
                    'attr' => [
                        'min' => 1,
                    ],
                ])
            ->end()
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }
}
