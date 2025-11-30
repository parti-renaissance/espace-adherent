<?php

declare(strict_types=1);

namespace App\Admin\LocalElection;

use App\Admin\AbstractAdmin;
use App\Entity\Geo\Zone;
use App\Entity\LocalElection\CandidaciesGroup;
use App\LocalElection\Manager;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class CandidaciesGroupAdmin extends AbstractAdmin
{
    public function __construct(private readonly Manager $localElectionManager)
    {
        parent::__construct();
    }

    protected function getAccessMapping(): array
    {
        return [
            'candidate_import' => 'CANDIDATE_IMPORT',
        ];
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
           ->add('candidate_import', $this->getRouterIdParameter().'/candidate-import')
        ;
    }

    public function configureActionButtons(array $buttonList, string $action, ?object $object = null): array
    {
        $actions = parent::configureActionButtons($buttonList, $action, $object);

        if ('edit' === $action) {
            if ($this->hasAccess('candidate_import', $object) && $this->hasRoute('candidate_import')) {
                $actions['candidate_import'] = ['template' => 'admin/local_election/candidate_import_button.html.twig'];
            }
        }

        return $actions;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'ID'])
            ->addIdentifier('election.designation.label', null, ['label' => 'Libellé'])
            ->add('election.designation.zones', null, ['label' => 'Zones'])
            ->add('candidacies', null, [
                'label' => 'Nombre de candidats',
                'virtual_field' => true,
                'template' => 'admin/local_election/list_candidacies_count.html.twig',
            ])
            ->add('createdAt', null, ['label' => 'Date de création'])
            ->add('updatedAt', null, ['label' => 'Date de modification'])
            ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var CandidaciesGroup $candidaciesGroup */
        $candidaciesGroup = $this->getSubject();

        $form
            ->tab('Général')
                ->with('Information')
                    ->add('election', ModelAutocompleteType::class, [
                        'label' => 'Élection',
                        'property' => 'designation.label',
                        'help' => \sprintf(
                            'Si vous ne trouvez pas la bonne élection, veuillez la créer en cliquant <a href="%s">ici</a>',
                            $this->getRouteGenerator()->generate('admin_app_localelection_localelection_create')
                        ),
                        'help_html' => true,
                        'btn_add' => false,
                    ])
                ->end()
                ->with('Profession de foi', ['box_class' => 'box box-success'])
                    ->add('file', FileType::class, [
                        'label' => false,
                        'attr' => ['accept' => 'application/pdf'],
                    ])
                ->end()
            ->end()
            ->tab('Candidat(e)s')
                ->with('Titulaires', ['description' => $candidaciesGroup->election ? 'Les candidat(e)s sont ordonnés par leur position.' : '<span class="text-danger">Veuillez d\'abord créer une liste afin de pouvoir y ajouter des candidats.</span>'])
                    ->add('candidacies', CollectionType::class, [
                        'label' => false,
                        'by_reference' => false,
                        'btn_add' => $candidaciesGroup->election ? 'Ajouter' : false,
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ])
                ->end()
                ->with('Suppléants')
                    ->add('substituteCandidacies', CollectionType::class, [
                        'label' => false,
                        'by_reference' => false,
                        'btn_add' => $candidaciesGroup->election ? 'Ajouter' : false,
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ])
                ->end()
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('election', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Élection',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => 'designation.label',
                ],
            ])
            ->add('election.designation.zones', ModelFilter::class, [
                'show_filter' => true,
                'label' => 'Zones',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'multiple' => true,
                    'property' => [
                        'name',
                        'code',
                    ],
                    'callback' => function (AdminInterface $admin, array $property, $value): void {
                        $datagrid = $admin->getDatagrid();
                        $query = $datagrid->getQuery();
                        $rootAlias = $query->getRootAlias();
                        $query
                            ->andWhere($rootAlias.'.type IN (:types)')
                            ->setParameter('types', [Zone::DEPARTMENT, Zone::REGION])
                        ;

                        $datagrid->setValue($property[0], null, $value);
                    },
                ],
            ])
        ;
    }

    public function toString($object): string
    {
        return (string) $object->election;
    }

    public function prePersist($object): void
    {
        $this->uploadFaithStatementFile($object);
    }

    public function preUpdate($object): void
    {
        $this->uploadFaithStatementFile($object);
    }

    private function uploadFaithStatementFile(CandidaciesGroup $candidaciesGroup): void
    {
        $this->localElectionManager->uploadFaithStatementFile($candidaciesGroup);
    }
}
