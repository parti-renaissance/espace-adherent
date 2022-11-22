<?php

namespace App\Admin\LocalElection;

use App\Admin\AbstractAdmin;
use App\Entity\Geo\Zone;
use App\Entity\LocalElection\CandidaciesGroup;
use League\Flysystem\FilesystemInterface;
use Ramsey\Uuid\Uuid;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class CandidaciesGroupAdmin extends AbstractAdmin
{
    private ?FilesystemInterface $storage = null;

    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add('election.designation.label', null, ['label' => 'Libellé'])
            ->add('election.designation.zones', null, ['label' => 'Zones'])
            ->add('candidacies', null, [
                'label' => 'Nombre de candidats',
                'virtual_field' => true,
                'template' => 'admin/local_election/list_candidacies_count.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, ListMapper::TYPE_ACTIONS, [
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        /** @var CandidaciesGroup $candidaciesGroup */
        $candidaciesGroup = $this->getSubject();

        $form
            ->tab('Général')
                ->with('Information')
                    ->add('election', ModelAutocompleteType::class, [
                        'label' => 'Élection',
                        'property' => 'designation.label',
                        'help' => sprintf(
                            'Si vous ne trouvez pas la bonne élection, veuillez la créer en cliquant <a href="%s">ici</a>',
                            $this->routeGenerator->generate('admin_app_localelection_localelection_create')
                        ),
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
                ->with(false, ['description' => $candidaciesGroup->election ? 'Les candidat(e)s sont ordonnés par leur position.' : '<span class="text-danger">Veuillez d\'abord créer une liste afin de pouvoir y ajouter des candidats.</span>'])
                    ->add('candidacies', CollectionType::class, [
                        'label' => false,
                        'by_reference' => false,
                        'btn_add' => (bool) $candidaciesGroup->election,
                    ], [
                        'edit' => 'inline',
                        'inline' => 'table',
                    ])
                ->end()
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('election', ModelAutocompleteFilter::class, [
                'show_filter' => true,
                'label' => 'Élection',
                'field_options' => [
                    'property' => 'designation.label',
                ],
            ])
            ->add('election.designation.zones', ModelAutocompleteFilter::class, [
                'show_filter' => true,
                'label' => 'Zones',
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
        if (!$candidaciesGroup->file) {
            return;
        }

        $candidaciesGroup->faithStatementFileName = sprintf('%s.pdf', Uuid::uuid4());

        $this->storage->put($candidaciesGroup->getFaitStatementFilePath(), file_get_contents($candidaciesGroup->file->getPathname()));
    }

    /** @required */
    public function setStorage(FilesystemInterface $storage): void
    {
        $this->storage = $storage;
    }
}
