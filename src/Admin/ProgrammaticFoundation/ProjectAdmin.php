<?php

namespace App\Admin\ProgrammaticFoundation;

use App\Entity\ProgrammaticFoundation\Measure;
use App\Entity\ProgrammaticFoundation\Project;
use App\Entity\ProgrammaticFoundation\Tag;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ProjectAdmin extends AbstractAdmin
{
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->addOrderBy('o.measure', 'ASC')
            ->addOrderBy('o.position', 'ASC')
        ;

        return $query;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('measure', null, [
                'label' => 'Mesure associée',
                'sortable' => true,
                'sort_parent_association_mappings' => [['fieldName' => 'measure']],
                'sort_field_mapping' => ['fieldName' => 'title'],
            ])
            ->add('city', null, [
                'label' => 'Ville',
            ])
            ->add('tags', null, [
                'label' => 'Tags',
            ])
            ->add('position', null, [
                'header_style' => 'width: 10%',
                'label' => 'Ordre d\'affichage',
            ])
            ->add('isExpanded', null, [
                'header_style' => 'width: 10%',
                'label' => 'Ouvert par défaut',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'header_style' => 'width: 15%',
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('city', ChoiceFilter::class, [
                'label' => 'Taille de ville',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => array_combine(Project::CITY_TYPES, Project::CITY_TYPES),
                ],
            ])
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

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Projet', ['class' => 'col-md-8'])
                ->add('measure', EntityType::class, [
                    'label' => 'Mesure associée',
                    'class' => Measure::class,
                    'placeholder' => 'Sélectionner une mesure',
                ])
                ->add('title', null, [
                    'label' => 'Titre',
                ])
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
                ])
                ->add('city', ChoiceType::class, [
                    'label' => 'Taille de ville',
                    'choices' => array_combine(Project::CITY_TYPES, Project::CITY_TYPES),
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
