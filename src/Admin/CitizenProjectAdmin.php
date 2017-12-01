<?php

namespace AppBundle\Admin;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use AppBundle\Form\UnitedNationsCountryType;
use AppBundle\Intl\UnitedNationsBundle;
use AppBundle\Repository\CitizenProjectMembershipRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CitizenProjectAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    private $manager;
    private $citizenProjectMembershipRepository;
    private $cachedDatagrid;

    public function __construct($code, $class, $baseControllerName, CitizenProjectManager $manager, CitizenProjectMembershipRepository $repository)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->manager = $manager;
        $this->citizenProjectMembershipRepository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagrid()
    {
        if (!$this->cachedDatagrid) {
            $this->cachedDatagrid = new CitizenProjectDatagrid(parent::getDatagrid(), $this->manager);
        }

        return $this->cachedDatagrid;
    }

    public function getTemplate($name)
    {
        if ('show' === $name) {
            return 'admin/citizen_project/show.html.twig';
        }

        if ('edit' === $name) {
            return 'admin/citizen_project/edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->with('Projet citoyen', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                ])
                ->add('status', null, [
                    'label' => 'Statut',
                ])
                ->add('createdAt', null, [
                    'label' => 'Date de création',
                ])
                ->add('approvedAt', null, [
                    'label' => 'Date d\'approbation',
                ])
                ->add('refusedAt', null, [
                    'label' => 'Date de refus',
                ])
            ->end()
            ->with('Adresse', ['class' => 'col-md-5'])
                ->add('postAddress.address', TextType::class, [
                    'label' => 'Rue',
                ])
                ->add('postAddress.postalCode', TextType::class, [
                    'label' => 'Code postal',
                ])
                ->add('postAddress.cityName', TextType::class, [
                    'label' => 'Ville',
                ])
                ->add('postAddress.country', UnitedNationsCountryType::class, [
                    'label' => 'Pays',
                ])
                ->add('postAddress.latitude', TextType::class, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', TextType::class, [
                    'label' => 'Longitude',
                ])
            ->end()
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Projet citoyen', ['class' => 'col-md-7'])
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                ])
            ->end()
            ->with('Localisation', ['class' => 'col-md-5'])
                ->add('postAddress.latitude', null, [
                    'required' => false,
                    'empty_data' => null,
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', null, [
                    'required' => false,
                    'empty_data' => null,
                    'label' => 'Longitude',
                    'help' => 'Pour modifier l\'adresse, impersonnifiez un administrateur de ce projet citoyen.',
                ])
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $citizenProjectMembershipRepository = $this->citizenProjectMembershipRepository;

        $datagridMapper
            ->add('id', null, [
                'label' => 'ID',
                'show_filter' => true,
            ])
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'field_type' => 'sonata_type_date_range_picker',
            ])
            ->add('administratorFirstName', CallbackFilter::class, [
                'label' => 'Prénom de l\'administrateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) use ($citizenProjectMembershipRepository) {
                    if (!$value['value']) {
                        return;
                    }

                    if (!$ids = $citizenProjectMembershipRepository->findCitizenProjectsUuidByAdministratorFirstName($value['value'])) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $qb->andWhere($qb->expr()->in(sprintf('%s.uuid', $alias), $ids));

                    return true;
                },
            ])
            ->add('administratorLastName', CallbackFilter::class, [
                'label' => 'Nom de l\'administrateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) use ($citizenProjectMembershipRepository) {
                    if (!$value['value']) {
                        return;
                    }

                    if (!$ids = $citizenProjectMembershipRepository->findCitizenProjectsUuidByAdministratorLastName($value['value'])) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $qb->andWhere($qb->expr()->in(sprintf('%s.uuid', $alias), $ids));

                    return true;
                },
            ])
            ->add('administratorEmailAddress', CallbackFilter::class, [
                'label' => 'Email de l\'administrateur',
                'show_filter' => true,
                'field_type' => EmailType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) use ($citizenProjectMembershipRepository) {
                    if (!$value['value']) {
                        return;
                    }

                    if (!$ids = $citizenProjectMembershipRepository->findCitizenProjectsUuidByAdministratorEmailAddress($value['value'])) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $qb->andWhere($qb->expr()->in(sprintf('%s.uuid', $alias), $ids));

                    return true;
                },
            ])
            ->add('postalCode', CallbackFilter::class, [
                'label' => 'Code postal',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('%s.postAddress.postalCode', $alias).' LIKE :postalCode');
                    $qb->setParameter('postalCode', $value['value'].'%');

                    return true;
                },
            ])
            ->add('city', CallbackFilter::class, [
                'label' => 'Ville',
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.cityName)', $alias).' LIKE :cityName');
                    $qb->setParameter('cityName', '%'.strtolower($value['value']).'%');

                    return true;
                },
            ])
            ->add('country', CallbackFilter::class, [
                'label' => 'Pays',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_flip(UnitedNationsBundle::getCountries()),
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.country)', $alias).' = :country');
                    $qb->setParameter('country', strtolower($value['value']));

                    return true;
                },
            ])
            ->add('assistanceNeeded', null, [
                'label' => 'Demande d\'accompagnement',
                'show_filter' => true,
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'En attente' => CitizenProject::PENDING,
                        'Accepté' => CitizenProject::APPROVED,
                        'Refusé' => CitizenProject::REFUSED,
                    ],
                ],
            ])
            ->add('creatorFirstName', CallbackFilter::class, [
                'label' => 'Prénom du créateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    /* @var ProxyQuery|QueryBuilder $qb */
                    $qb
                        ->leftJoin(Adherent::class, 'creator', Join::WITH, sprintf('creator.uuid=%s.createdBy', $alias))
                        ->andWhere('LOWER(creator.firstName) LIKE :firstName ')
                        ->setParameter('firstName', sprintf('%%%s%%', mb_strtolower($value['value'])));

                    return true;
                },
            ])
            ->add('creatorLastName', CallbackFilter::class, [
                'label' => 'Nom du créateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    /** @var ProxyQuery|QueryBuilder $qb */
                    if (!in_array('creator', $qb->getAllAliases(), true)) {
                        $qb->leftJoin(Adherent::class, 'creator', Join::WITH, sprintf('creator.uuid=%s.createdBy', $alias));
                    }

                    $qb->andWhere('LOWER(creator.lastName) LIKE :lastName')
                        ->setParameter('lastName', sprintf('%%%s%%', mb_strtolower($value['value'])));

                    return true;
                },
            ])
            ->add('creatorEmailAddress', CallbackFilter::class, [
                'label' => 'Email du créateur',
                'show_filter' => true,
                'field_type' => EmailType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return;
                    }

                    /** @var ProxyQuery|QueryBuilder $qb */
                    if (!in_array('creator', $qb->getAllAliases(), true)) {
                        $qb->leftJoin(Adherent::class, 'creator', Join::WITH, sprintf('creator.uuid=%s.createdBy', $alias));
                    }

                    $qb->andWhere('creator.emailAddress=:emailAddress')
                        ->setParameter('emailAddress', mb_strtolower($value['value']));

                    return true;
                },
            ])
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('category', null, [
                'label' => 'Catégorie(s)',
            ])
            ->add('administrators', TextType::class, [
                'label' => 'Organisateur(s)/Co-Organisateur(s)',
                'template' => 'admin/citizen_project/list_administrators.html.twig',
            ])
            ->add('membersCounts', null, [
                'label' => 'Membres',
            ])
            ->add('postAddress.cityName', null, [
                'label' => 'Ville',
            ])
            ->add('assistanceNeeded', null, [
                'label' => 'Demande d\'accompagnement',
            ])
            ->add('status', TextType::class, [
                'label' => 'Statut',
                'template' => 'admin/citizen_project/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/citizen_project/list_actions.html.twig',
            ])
            ->add('creator', null, [
                'label' => 'Créateur',
                'template' => 'admin/citizen_project/list_creator.html.twig',
            ])
            ->add('postAddress.country', null, [
                'label' => 'Pays',
            ])
            ->add('postAddress.postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('problemDescription', null, [
                'label' => 'Problème local adressé',
            ])
            ->add('proposedSolution', null, [
                'label' => 'Réponse au problème',
            ])
        ;
    }
}
