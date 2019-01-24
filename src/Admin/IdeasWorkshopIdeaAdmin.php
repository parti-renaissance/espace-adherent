<?php

namespace AppBundle\Admin;

use AppBundle\Entity\IdeasWorkshop\IdeaStatusEnum;
use AppBundle\Repository\IdeaRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class IdeasWorkshopIdeaAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    private $cachedDatagrid;
    private $ideaRepository;

    public function __construct($code, $class, $baseControllerName, IdeaRepository $repository)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->ideaRepository = $repository;
    }

    public function getDatagrid()
    {
        if (!$this->cachedDatagrid) {
            $this->cachedDatagrid = new IdeaDatagrid(parent::getDatagrid(), $this->ideaRepository);
        }

        return $this->cachedDatagrid;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('create');
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('daysBeforeDeadline', null, [
                'label' => 'Temps restant',
            ])
            ->add('committee', null, [
                'label' => 'Comité associé',
            ])
            ->add('themes', null, [
                'label' => 'Thème',
            ])
            ->add('category', null, [
                'label' => 'Echelle du projet',
            ])
            ->add('needs', null, [
                'label' => 'Besoins',
            ])
            ->add('status', null, [
                'label' => 'Statut',
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('committee', null, [
                'label' => 'Comité associé',
            ])
            ->add('themes', null, [
                'label' => 'Thème',
            ])
            ->add('category', null, [
                'label' => 'Echelle du projet',
            ])
            ->add('needs', null, [
                'label' => 'Besoins',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('author.firstName', null, [
                'label' => 'Prénom du créateur',
            ])
            ->add('author.lastName', null, [
                'label' => 'Nom du créateur',
            ])
            ->add('author.emailAddress', null, [
                'label' => 'Mail du créateur',
            ])
            ->add('status', CallbackFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_combine(IdeaStatusEnum::ALL_STATUSES, IdeaStatusEnum::ALL_STATUSES),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$status = $value['value']) {
                        return;
                    }

                    $this->ideaRepository->addStatusFilter($qb, $alias, $status);

                    return true;
                },
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
                'header_style' => 'width: 250px',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('daysBeforeDeadline', null, [
                'label' => 'Temps restant',
                'template' => 'admin/ideas_workshop/idea/list_deadline.html.twig',
            ])
            ->add('author', null, [
                'label' => 'Créateur',
                'template' => 'admin/list/list_author.html.twig',
            ])
            ->add('committee', null, [
                'label' => 'Comité associé',
            ])
            ->add('themes', null, [
                'label' => 'Thème',
            ])
            ->add('category', null, [
                'label' => 'Echelle du projet',
            ])
            ->add('contributorsCount', null, [
                'label' => 'Nombre de contributeurs',
            ])
            ->add('needs', null, [
                'label' => 'Besoins',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/ideas_workshop/idea/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'preview' => [
                        'template' => 'admin/ideas_workshop/idea/list_show_page.html.twig',
                    ],
                    'show_contributors' => [
                        'template' => 'admin/ideas_workshop/idea/list_show_contributors.html.twig',
                    ],
                    'moderate' => [
                        'template' => 'admin/ideas_workshop/list_action_moderate.html.twig',
                    ],
                    'contribute' => [
                        'template' => 'admin/ideas_workshop/idea/list_action_contribute.html.twig',
                    ],
                    'finalize' => [
                        'template' => 'admin/ideas_workshop/idea/list_action_finalize.html.twig',
                    ],
                    'delete' => [],
                ],
            ])
        ;
    }
}
