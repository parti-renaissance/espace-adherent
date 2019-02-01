<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Report\ReportReasonEnum;
use AppBundle\Entity\Report\ReportStatusEnum;
use AppBundle\Report\ReportType;
use AppBundle\Repository\ReportRepository;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType as FilterChoiceType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\CoreBundle\Form\Type\DateRangePickerType;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\CallbackFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReportAdmin extends AbstractAdmin
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    protected $datagridValues = [
        'status' => ['type' => FilterChoiceType::TYPE_EQUAL, 'value' => ReportStatusEnum::STATUS_UNRESOLVED],
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];
    private $reportRepository;

    public function __construct(string $code, string $class, string $baseControllerName, ReportRepository $reportRepository)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->reportRepository = $reportRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(['list', 'show']);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id', null, [
                'label' => 'ID',
                'show_filter' => true,
                'field_type' => TextType::class,
            ])
            ->add('uuid', null, [
                'label' => 'UUID',
                'show_filter' => true,
                'field_type' => TextType::class,
            ])
            ->add('type', CallbackFilter::class, [
                'label' => 'Type d\'objet signalé',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => ReportType::LIST,
                    'choice_translation_domain' => 'reports',
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb->andWhere(sprintf('%s INSTANCE OF %s', $alias, $value['value']));

                    return true;
                },
            ])
            ->add('subjectName', CallbackFilter::class, [
                'label' => 'Nom de l\'objet',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $ids = $this->reportRepository->findIdsByNameForAll($value['value']);

                    if (!$ids) {
                        return true;
                    }

                    /* @var ProxyQuery|QueryBuilder $qb */
                    $qb->andWhere($qb->expr()->in("$alias.id", $ids));

                    return true;
                },
            ])
            ->add('authorFirstName', CallbackFilter::class, [
                'label' => 'Prénom du requêteur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    /* @var ProxyQuery|QueryBuilder $qb */
                    $qb
                        ->join("$alias.author", 'author')
                        ->andWhere('LOWER(author.firstName) LIKE :firstName')
                        ->setParameter('firstName', sprintf('%%%s%%', mb_strtolower($value['value'])))
                    ;

                    return true;
                },
            ])
            ->add('authorLastName', CallbackFilter::class, [
                'label' => 'Nom du requêteur',
                'show_filter' => true,
                'field_type' => TextType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    /* @var ProxyQuery|QueryBuilder $qb */
                    if (!\in_array('author', $qb->getAllAliases(), true)) {
                        $qb->join("$alias.author", 'author');
                    }

                    $qb
                        ->andWhere('LOWER(author.lastName) LIKE :lastName')
                        ->setParameter('lastName', sprintf('%%%s%%', mb_strtolower($value['value'])))
                    ;

                    return true;
                },
            ])
            ->add('authorEmailAddress', CallbackFilter::class, [
                'label' => 'Email du requêteur',
                'show_filter' => true,
                'field_type' => EmailType::class,
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    /* @var ProxyQuery|QueryBuilder $qb */
                    if (!\in_array('author', $qb->getAllAliases(), true)) {
                        $qb->join("$alias.author", 'author');
                    }

                    $qb
                        ->andWhere('author.emailAddress=:emailAddress')
                        ->setParameter('emailAddress', mb_strtolower($value['value']))
                    ;

                    return true;
                },
            ])
            ->add('reasons', CallbackFilter::class, [
                'label' => 'Raisons',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_combine(ReportReasonEnum::REASONS_LIST, ReportReasonEnum::REASONS_LIST),
                    'choice_translation_domain' => 'reports',
                ],
                'callback' => function (ProxyQuery $qb, string $alias, string $field, array $value) {
                    if (!$value['value']) {
                        return false;
                    }

                    $qb->andWhere($qb->expr()->eq(sprintf('json_contains(%s.reasons, :reason)', $alias), 1));
                    $qb->setParameter(':reason', sprintf('"%s"', $value['value']));

                    return true;
                },
            ])
            ->add('comment', null, [
                'label' => 'Commentaire',
                'show_filter' => true,
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => array_combine(ReportStatusEnum::toArray(), ReportStatusEnum::toArray()),
                    'choice_translation_domain' => 'reports',
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'show_filter' => true,
                'label' => 'Date de signalement',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('resolvedAt', DateRangeFilter::class, [
                'show_filter' => true,
                'label' => 'Date de résolution',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'label' => 'ID',
                'route' => ['name' => 'show'],
            ])
            ->add('type', 'trans', [
                'label' => 'Type d\'objet signalé',
                'catalogue' => 'reports',
            ])
            ->add('subject', null, [
                'label' => 'Nom de l\'objet',
                'template' => 'admin/report/list_subject.html.twig', // This is needed because the concrette class type of objects is not detected for list
            ])
            ->add('author', null, [
                'label' => 'Requêteur',
                'template' => 'admin/report/list_author.html.twig',
            ])
            ->add('reasons', null, [
                'label' => 'Raisons',
                'template' => 'admin/report/list_reasons.html.twig',
            ])
            ->add('comment', null, [
                'label' => 'Commentaire',
            ])
            ->add('resolved', 'boolean', [
                'label' => 'Statut',
                'template' => 'admin/report/list_status.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date du signalement',
                'format' => self::DATE_FORMAT,
            ])
            ->add('resolvedAt', null, [
                'label' => 'Date de résolution',
                'format' => self::DATE_FORMAT,
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
                'template' => 'admin/report/list_actions.html.twig',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('uuid', null, [
                'label' => 'UUID',
            ])
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('type', 'trans', [
                'label' => 'Type d\'objet signalé',
                'catalogue' => 'reports',
            ])
            ->add('subject', null, [
                'label' => 'Nom de l\'objet signalé',
                'route' => ['name' => 'show'],
            ])
            ->add('author', null, [
                'label' => 'Requêteur',
                'template' => 'admin/report/show_author.html.twig',
            ])
            ->add('reasons', null, [
                'label' => 'Raisons',
                'template' => 'admin/report/show_reasons.html.twig',
            ])
            ->add('comment', null, [
                'label' => 'Commentaire',
            ])
            ->add('resolved', 'boolean', [
                'label' => 'Statut',
                'template' => 'admin/report/show_status.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date du signalement',
                'format' => self::DATE_FORMAT,
            ])
            ->add('resolvedAt', null, [
                'label' => 'Date de résolution',
                'format' => self::DATE_FORMAT,
            ])
        ;
    }
}
