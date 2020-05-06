<?php

namespace App\Admin;

use App\Entity\Referent;
use App\Form\GenderType;
use App\Repository\ReferentOrganizationalChart\OrganizationalChartItemRepository;
use App\ValueObject\Genders;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ReferentAdmin extends AbstractAdmin
{
    public $OCItems = [];
    protected $datagridValues = [
        '_page' => 1,
        '_sort_order' => 'ASC',
        '_sort_by' => 'lastName',
    ];
    protected $maxPerPage = 100;
    protected $perPageOptions = [];
    protected $formOptions = [
        'validation_groups' => ['Default', 'Admin'],
    ];
    private $dataTransformer;
    private $organizationalChartItemRepository;

    public function __construct(
        string $code,
        string $class,
        string $baseControllerName,
        DataTransformerInterface $dataTransformer,
        OrganizationalChartItemRepository $organizationalChartItemRepository
    ) {
        parent::__construct($code, $class, $baseControllerName);

        $this->dataTransformer = $dataTransformer;
        $this->organizationalChartItemRepository = $organizationalChartItemRepository;
    }

    protected function configureDatagridFilters(DatagridMapper $mapper)
    {
        $mapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('gender', null, [
                'label' => 'Genre',
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => Genders::CHOICES,
                ],
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
                'show_filter' => true,
            ])
            ->add('status', null, [
                'label' => 'Visibilité',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $mapper)
    {
        $mapper
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('_thumbnail', null, [
                'label' => 'Photo',
                'virtual_field' => true,
                'template' => 'admin/legislative_candidate/list_thumbnail.html.twig',
            ])
            ->add('firstName', null, [
                'label' => 'Prénom',
            ])
            ->add('lastName', null, [
                'label' => 'Nom',
            ])
            ->add('emailAddress', null, [
                'label' => 'Email',
            ])
            ->add('areas', null, [
                'label' => 'Zone',
            ])
            ->add('referentPersonLinks', null, [
                'label' => 'Équipe départementale',
                'associated_property' => 'getAdminDisplay',
            ])
            ->add('status', null, [
                'label' => 'Visibilité',
                'show_filter' => true,
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'export_team_xlsx' => [
                        'template' => 'admin/referent/export_team_xlsx_button.html.twig',
                    ],
                    'show' => [],
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $mapper)
    {
        $mapper
            ->with('Informations personnelles', ['class' => 'col-md-4'])
                ->add('status', ChoiceType::class, [
                    'label' => 'Visibilité',
                    'choices' => [
                        'Visible' => Referent::ENABLED,
                        'Masqué' => Referent::DISABLED,
                    ],
                ])
                ->add('gender', GenderType::class, [
                    'label' => 'Genre',
                    'expanded' => false,
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                    'attr' => [
                        'placeholder' => 'Dumoulin',
                    ],
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                    'attr' => [
                        'placeholder' => 'Alexandre',
                    ],
                ])
                ->add('emailAddress', EmailType::class, [
                    'label' => 'Adresse e-mail',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'alexandre@dumoulin.com',
                    ],
                ])
                ->add('slug', null, [
                    'label' => 'Identifiant URL',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'alexandre-dumoulin',
                    ],
                    'help' => 'Laissez le champ vide pour que le système génère cette valeur automatiquement',
                ])
            ->end()
            ->with('Zone', ['class' => 'col-md-4'])
                ->add('areas', TextType::class, [
                    'label' => 'Zone',
                    'required' => true,
                ])
                ->add('geojson', TextareaType::class, [
                    'label' => 'Données GeoJSON',
                    'required' => false,
                ])
                ->add('areaLabel', TextType::class, [
                    'label' => 'Nom de la zone',
                    'required' => true,
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-4'])
                ->add('media', null, [
                    'label' => false,
                ])
            ->end()
            ->with('Parcours personnel', ['class' => 'col-md-9'])
                ->add('description', TextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
            ->end()
            ->with('Liens Web', ['class' => 'col-md-3'])
                ->add('twitterPageUrl', UrlType::class, [
                    'label' => 'Page Twitter',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'https://twitter.com/alexandredumoulin',
                    ],
                ])
                ->add('facebookPageUrl', UrlType::class, [
                    'label' => 'Page Facebook',
                    'required' => false,
                    'attr' => [
                        'placeholder' => 'https://facebook.com/alexandre-dumoulin',
                    ],
                ])
            ->end()
        ;
        $mapper->get('areas')->addModelTransformer($this->dataTransformer);
    }

    protected function configureShowFields(ShowMapper $mapper)
    {
        $this->OCItems = $this->organizationalChartItemRepository->getRootNodes();

        $mapper
            ->with('Informations générales', ['class' => 'col-md-5'])
                ->add('id', null, [
                    'label' => 'ID',
                ])
                ->add('status', null, [
                    'label' => 'Visibilité',
                ])
                ->add('gender', null, [
                    'label' => 'Genre',
                ])
                ->add('lastName', null, [
                    'label' => 'Nom',
                ])
                ->add('firstName', null, [
                    'label' => 'Prénom',
                ])
                ->add('emailAddress', null, [
                    'label' => 'Adresse e-mail',
                ])
            ->end()
            ->with('Zone', ['class' => 'col-md-7'])
                ->add('geojson', null, [
                    'label' => 'Données GeoJSON',
                ])
                ->add('areas', null, [
                    'label' => 'Zones',
                ])
            ->end()
            ->with('Photo de profil', ['class' => 'col-md-5'])
                ->add('media', null)
            ->end()
            ->with('Pages Web', ['class' => 'col-md-7'])
                ->add('twitterPageUrl', 'url', [
                    'label' => 'Twitter',
                ])
                ->add('facebookPageUrl', 'url', [
                    'label' => 'Facebook',
                ])
            ->end()
            ->with('Description', ['class' => 'col-md-12'])
                ->add('description', null)
            ->end()
            ->with('Organigrame', ['class' => 'col-md-12'])
                ->add('referentPersonLinks', null, [
                    'template' => 'admin/referent/organization_chart.html.twig',
                ])
            ->end()
        ;
    }

    protected function configureBatchActions($actions)
    {
        $actions['exportTeams'] = [
            'label' => 'Exporter les équipes',
            'ask_confirmation' => false,
        ];

        return $actions;
    }
}
