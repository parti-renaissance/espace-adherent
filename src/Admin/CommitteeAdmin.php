<?php

namespace AppBundle\Admin;

use AppBundle\Committee\CommitteeManager;
use AppBundle\Entity\{Committee, CommitteeMembership};
use AppBundle\Form\UnitedNationsCountryType;
use AppBundle\Intl\UnitedNationsBundle;
use Doctrine\Common\Persistence\ObjectManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\{ListMapper, DatagridMapper};
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\{CallbackFilter, DateRangeFilter};
use Symfony\Component\Form\Extension\Core\Type\{ChoiceType, EmailType, TextareaType, TextType, UrlType};

class CommitteeAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];
    /**
     * @var CommitteeManager
     */
    private $_manager;
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    private $_committeeMembershipRepository;
    /**
     * @var
     */
    private $_cachedDatagrid;

    public function __construct($code, $class, $baseControllerName, CommitteeManager $manager, ObjectManager $om)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->_manager = $manager;
        $this->_committeeMembershipRepository = $om->getRepository(CommitteeMembership::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagrid()
    {
        if (!$this->_cachedDatagrid) {
            $this->_cachedDatagrid = new CommitteeDatagrid(parent::getDatagrid(), $this->_manager);
        }

        return $this->_cachedDatagrid;
    }

    public function getTemplate($name)
    {
        return $name === 'show' ?
            'admin/committee/show.html.twig' :
            $name === 'edit' ?
                'admin/committee/edit.html.twig':
                parent::getTemplate($name);
    }

    protected function configureShowFields(ShowMapper $showMapper)
        : void
    {
        $showMapper
            ->with('Comité', array('class' => 'col-md-7'))
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('description', TextareaType::class, [
                    'filter_emojis' => true,
                    'label' => 'Description',
                    'attr' => [
                        'rows' => '3',
                    ],
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                ])
                ->add('facebookPageUrl', UrlType::class, [
                    'label' => 'Facebook',
                ])
                ->add('twitterNickname', null, [
                    'label' => 'Twitter',
                ])
                ->add('googlePlusPageUrl', UrlType::class, [
                    'label' => 'Google+',
                ])
                ->add('status', null, [
                    'label' => 'Status',
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
            ->with('Adresse', array('class' => 'col-md-5'))
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
        : void
    {
        $formMapper
            ->with('Comité', array('class' => 'col-md-7'))
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                    'attr' => [
                        'rows' => '3',
                    ],
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                ])
                ->add('facebookPageUrl', UrlType::class, [
                    'label' => 'Facebook',
                    'required' => false,
                ])
                ->add('twitterNickname', null, [
                    'label' => 'Twitter',
                    'required' => false,
                ])
                ->add('googlePlusPageUrl', UrlType::class, [
                    'label' => 'Google+',
                    'required' => false,
                ])
            ->end()
            ->with('Localisation', array('class' => 'col-md-5'))
                ->add('postAddress.latitude', TextType::class, [
                    'label' => 'Latitude',
                ])
                ->add('postAddress.longitude', TextType::class, [
                    'label' => 'Longitude',
                    'help' => 'Pour modifier l\'adresse, impersonnifiez un animateur de ce comité.',
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        : void
    {
        $committeeMembershipRepository = $this->_committeeMembershipRepository;

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
            ->add('hostFirstName', CallbackFilter::class, [
                'label' => 'Prénom de l\'animateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) use ($committeeMembershipRepository) {
                    if (!$value['value']) {
                        return;
                    }

                    if (!$ids = $committeeMembershipRepository->findCommitteesUuidByHostFirstName($value['value'])) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $qb->andWhere($qb->expr()->in(sprintf('%s.uuid', $alias), $ids));

                    return true;
                },
            ])
            ->add('hostLastName', CallbackFilter::class, [
                'label' => 'Nom de l\'animateur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) use ($committeeMembershipRepository) {
                    if (!$value['value']) {
                        return;
                    }

                    if (!$ids = $committeeMembershipRepository->findCommitteesUuidByHostLastName($value['value'])) {
                        // Force no results when no user is found
                        $qb->andWhere($qb->expr()->in(sprintf('%s.id', $alias), [0]));

                        return true;
                    }

                    $qb->andWhere($qb->expr()->in(sprintf('%s.uuid', $alias), $ids));

                    return true;
                },
            ])
            ->add('hostEmailAddress', CallbackFilter::class, [
                'label' => 'Email de l\'animateur',
                'show_filter' => true,
                'field_type' => EmailType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) use ($committeeMembershipRepository) {
                    if (!$value['value']) {
                        return;
                    }

                    if (!$ids = $committeeMembershipRepository->findCommitteesUuidByHostEmailAddress($value['value'])) {
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
                        //@TODO type
                        return;
                    }

                    $qb->andWhere(sprintf('LOWER(%s.postAddress.country)', $alias).' = :country');
                    $qb->setParameter('country', strtolower($value['value']));

                    return true;
                },
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => [
                        'En attente' => Committee::PENDING,
                        'Accepté' => Committee::APPROVED,
                        'Refusé' => Committee::REFUSED,
                    ],
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
        : void
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('postAddress.postalCode', null, [
                'label' => 'Code postal',
            ])
            ->add('postAddress.cityName', null, [
                'label' => 'Ville',
            ])
            ->add('postAddress.country', null, [
                'label' => 'Pays',
            ])
            ->add('membersCounts', null, [
                'label' => 'Membres',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('hosts', TextType::class, [
                'label' => 'Animateur(s)',
                'template' => 'admin/committee/list_hosts.html.twig',
            ])
            ->add('status', TextType::class, [
                'label' => 'Statut',
                'template' => 'admin/committee/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/committee/list_actions.html.twig',
            ])
        ;
    }
}
