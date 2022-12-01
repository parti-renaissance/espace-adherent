<?php

namespace App\Admin\ProgrammaticFoundation;

use App\Entity\ProgrammaticFoundation\SubApproach;
use App\Entity\ProgrammaticFoundation\Tag;
use App\Form\PurifiedTextareaType;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class MeasureAdmin extends AbstractAdmin
{
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        /** @var QueryBuilder $query */
        $query->addOrderBy('o.subApproach', 'ASC');
        $query->addOrderBy('o.position', 'ASC');

        return $query;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('subApproach', null, [
                'label' => 'Axe secondaire associé',
                'sortable' => true,
                'sort_parent_association_mappings' => [['fieldName' => 'approach']],
                'sort_field_mapping' => ['fieldName' => 'title'],
            ])
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
            ])
            ->add('isLeading', null, [
                'label' => 'Mesure phare',
            ])
            ->add('isExpanded', null, [
                'label' => 'Ouvert par défaut',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'link' => [
                        'template' => 'admin/programmatic_foundation/measure/link.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('isLeading', null, [
                'label' => 'Mesure phare',
            ])
            ->add('isExpanded', null, [
                'label' => 'Ouvert par défaut',
            ])
            ->add('subApproach', null, [
                'label' => 'Axe secondaire associé',
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

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->with('Mesure', ['class' => 'col-md-8'])
                ->add('subApproach', EntityType::class, [
                    'label' => 'Axe secondaire associé associé',
                    'class' => SubApproach::class,
                    'placeholder' => 'Sélectionner un axe secondaire',
                ])
                ->add('title', null, [
                    'label' => 'Titre',
                ])
                ->add('isLeading', null, [
                    'label' => 'Mesure phare',
                ])
                ->add('content', PurifiedTextareaType::class, [
                    'label' => 'Contenu',
                    'attr' => ['class' => 'ck-editor-advanced'],
                    'purify_html_profile' => 'enrich_content',
                ])
                ->add('isExpanded', null, [
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

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }
}
