<?php

namespace AppBundle\Admin\ProgrammaticFoundation;

use AppBundle\Entity\ProgrammaticFoundation\Measure;
use AppBundle\Entity\ProgrammaticFoundation\Tag;
use AppBundle\Form\PurifiedTextareaType;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\BooleanType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProjectAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
    ];

    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = parent::createQuery($context);
        $proxyQuery->addOrderBy('o.measure', 'ASC');
        $proxyQuery->addOrderBy('o.position', 'ASC');

        return $proxyQuery;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->add('measure', null, [
                'label' => 'Mesure associée',
                'sortable' => true,
                'sort_parent_association_mappings' => [['fieldName' => 'measure']],
                'sort_field_mapping' => ['fieldName' => 'title'],
            ])
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('city', null, [
                'label' => 'Ville',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
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
            ->add('measure', null, [
                'label' => 'Mesure associée',
                'show_filter' => true,
            ])
            ->add('tags', ModelFilter::class, [
                'label' => 'Tags',
                'show_filter' => true,
                'field_options' => [
                    'class' => Tag::class,
                    'multiple' => true,
                ],
                'mapping_type' => ClassMetadata::MANY_TO_MANY,
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Projet', ['class' => 'col-md-8'])
                ->add('measure', EntityType::class, [
                    'label' => 'Mesure associée',
                    'class' => Measure::class,
                    'placeholder' => 'Sélectionner une mesure',
                ])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('content', PurifiedTextareaType::class, [
                    'label' => 'Contenu',
                    'attr' => ['class' => 'ck-editor-advanced'],
                    'purifier_type' => 'enrich_content',
                    'filter_emojis' => true,
                ])
                ->add('city', TextType::class, [
                    'label' => 'Ville',
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
            ->with('Éléments', ['class' => 'col-md-4'])
                ->add('tags', EntityType::class, [
                    'label' => 'Tags',
                    'required' => false,
                    'multiple' => true,
                    'class' => Tag::class,
                    'by_reference' => false,
                ])
            ->end()
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }
}
