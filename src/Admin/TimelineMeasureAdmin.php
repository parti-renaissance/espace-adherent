<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Timeline\Measure;
use AppBundle\Timeline\MeasureManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TimelineMeasureAdmin extends AbstractAdmin
{
    private $measureManager;

    public function __construct($code, $class, $baseControllerName, MeasureManager $measureManager)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->measureManager = $measureManager;
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery();
        $alias = $query->getRootAlias();

        $query
            ->leftJoin("$alias.translations", 'translations')
            ->addSelect('translations')
            ->leftJoin("$alias.themes", 'themes')
            ->addSelect('themes')
            ->leftJoin("$alias.profiles", 'profiles')
            ->addSelect('profiles')
        ;

        return $query;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Traductions', ['class' => 'col-md-6'])
                ->add('translations', TranslationsType::class, [
                    'by_reference' => false,
                    'label' => false,
                    'default_locale' => ['fr'],
                    'locales' => ['fr', 'en'],
                    //'required_locales' => ['fr', 'en'],
                    'fields' => [
                        'title' => [
                            'label' => 'Titre',
                        ],
                    ],
                ])
            ->end()
            ->with('Méta-données', ['class' => 'col-md-6'])
                ->add('link', null, [
                    'label' => 'Lien',
                    'required' => false,
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => Measure::STATUSES,
                ])
            ->end()
            ->with('Tags', ['class' => 'col-md-6'])
                ->add('profiles', null, [
                    'label' => 'Profils',
                ])
                ->add('themes', null, [
                    'label' => 'Thèmes',
                ])
                ->add('major', null, [
                    'label' => 'Mise en avant (32)',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('profiles', null, [
                'label' => 'Profils',
                'show_filter' => true,
            ], null, ['multiple' => true])
            ->add('themes', null, [
                'label' => 'Thèmes',
                'show_filter' => true,
            ], null, ['multiple' => true])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => Measure::STATUSES,
                ],
            ])
            ->add('major', null, [
                'label' => 'Mise en avant (32)',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', TextType::class, [
                'virtual_field' => true,
                'label' => 'Titre',
                'template' => 'admin/timeline/measure/list_title.html.twig',
            ])
            ->add('profiles', TextType::class, [
                'label' => 'Profils',
            ])
            ->add('themes', TextType::class, [
                'label' => 'Thèmes',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de modification',
            ])
            ->add('status', TextType::class, [
                'label' => 'Statut',
                'template' => 'admin/timeline/measure/list_status.html.twig',
            ])
            ->add('major', null, [
                'label' => 'Mise en avant (32)',
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


    /**
     * @param Measure $object
     */
    public function postPersist($object)
    {
        $this->measureManager->postPersist($object);
    }

    /**
     * @param Measure $object
     */
    public function postUpdate($object)
    {
        $this->measureManager->postUpdate($object);
    }

    /**
     * @param Measure $object
     */
    public function postRemove($object)
    {
        $this->measureManager->postRemove($object);
    }
}
