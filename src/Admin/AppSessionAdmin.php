<?php

namespace App\Admin;

use App\AppSession\SessionStatusEnum;
use App\AppSession\SystemEnum;
use App\Entity\Adherent;
use App\Entity\AppSession;
use App\Repository\AppSessionRepository;
use App\Repository\OAuth\ClientRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class AppSessionAdmin extends AbstractAdmin
{
    public function __construct(
        private readonly AppSessionRepository $appSessionRepository,
        private readonly ClientRepository $clientRepository,
    ) {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::SORT_BY] = 'lastActivityDate';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->clearExcept(['list', 'show']);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('adherent', ModelFilter::class, [
                'label' => 'Militant',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => ['property' => ['search']],
                'to_string_callback' => static function (Adherent $adherent): string {
                    return \sprintf(
                        '%s (%s) [%s]',
                        $adherent->getFullName(),
                        $adherent->getEmailAddress(),
                        $adherent->getPublicId()
                    );
                },
            ])
            ->add('status', ChoiceFilter::class, [
                'label' => 'Statut',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => SessionStatusEnum::all(),
                    'choice_label' => fn (SessionStatusEnum $status) => $status->value,
                ],
            ])
            ->add('client', ModelFilter::class, [
                'label' => 'App',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'minimum_input_length' => 0,
                    'property' => ['name'],
                    'callback' => [$this, 'prepareClientFilterCallback'],
                ],
            ])
            ->add('appSystem', ChoiceFilter::class, [
                'label' => 'Système',
                'show_filter' => true,
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'multiple' => true,
                    'choices' => SystemEnum::all(),
                    'choice_label' => fn (SystemEnum $system) => $system->value,
                ],
            ])
            ->add('appVersion', null, [
                'label' => 'Version',
                'show_filter' => true,
            ])
            ->add('pushTokenLinks.pushToken.identifier', null, [
                'label' => 'Push token',
                'show_filter' => true,
            ])
            ->add('lastActivityDate', DateTimeRangeFilter::class, [
                'label' => 'Dernière activité',
                'field_type' => DateTimeRangePickerType::class,
            ])
            ->add('createdAt', DateTimeRangeFilter::class, [
                'label' => 'Créée le',
                'field_type' => DateTimeRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('adherent.publicId', null, ['label' => 'PID'])
            ->add('adherent', null, [
                'label' => 'Prénom Nom',
                'template' => 'admin/adherent/list_fullname_certified.html.twig',
            ])
            ->add('status', 'enum', [
                'label' => 'Statut',
                'use_value' => true,
                'enum_translation_domain' => 'messages',
            ])
            ->add('client', null, ['label' => 'App'])
            ->add('appSystem', 'enum', [
                'label' => 'Système',
                'use_value' => true,
            ])
            ->add('appVersion', null, ['label' => 'Version'])
            ->add('userAgent', null, [
                'label' => 'Détail système',
                'template' => 'admin/app_session/list_system_detail.html.twig',
                'sortable' => false,
            ])
            ->add('isSubscribed', 'boolean', ['label' => 'Abonné'])
            ->add('createdAt', null, ['label' => 'Date de création'])
            ->add('lastActivityDate', null, ['label' => 'Dernière activité'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['show' => []]])
        ;
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->with('Session', ['class' => 'col-md-8'])
                ->add('adherent.publicId', null, ['label' => 'PID'])
                ->add('adherent', null, [
                    'label' => 'Prénom Nom',
                    'template' => 'admin/adherent/show_fullname_certified.html.twig',
                ])
                ->add('status', 'enum', [
                    'label' => 'Statut',
                    'use_value' => true,
                    'enum_translation_domain' => 'messages',
                ])
                ->add('isSubscribed', 'boolean', ['label' => 'Abonné'])
                ->add('client', null, ['label' => 'App'])
                ->add('appSystem', 'enum', [
                    'label' => 'Système',
                    'use_value' => true,
                ])
                ->add('appVersion', null, ['label' => 'Version'])
                ->add('uuid', null, ['label' => 'UUID'])
                ->add('userAgent', null, ['label' => 'User-Agent'])
                ->add('systemDetail', null, [
                    'label' => 'Détail système',
                    'template' => 'admin/app_session/show_system_detail.html.twig',
                    'virtual_field' => true,
                ])
                ->add('ip')
            ->end()
            ->with('Dates', ['class' => 'col-md-4'])
                ->add('createdAt', null, ['label' => 'Créée le'])
                ->add('updatedAt', null, ['label' => 'Modifiée le'])
                ->add('lastActivityDate', null, ['label' => 'Dernière activité'])
            ->end()
            ->with('Push tokens')
                ->add('pushTokenLinks', null, [
                    'label' => false,
                    'associated_property' => 'pushToken.identifier',
                    'template' => 'admin/app_session/show_push_tokens.html.twig',
                ])
            ->end()
        ;
    }

    public function prepareClientFilterCallback(AbstractAdmin $admin): void
    {
        /** @var QueryBuilder $qb */
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $qb
            ->innerJoin(AppSession::class, 'session', Join::WITH, 'session.client = '.$alias)
            ->groupBy($alias.'.id')
        ;
    }

    public function getListStats(): array
    {
        $cadreClient = $this->clientRepository->getCadreClient();
        $voxClient = $this->clientRepository->getVoxClient();

        return [
            'active_sessions_cadre' => $this->appSessionRepository->countActiveSessions($cadreClient),
            'active_sessions_vox' => $this->appSessionRepository->countActiveSessions($voxClient),
            'active_sessions_vox_web' => $this->appSessionRepository->countActiveSessions($voxClient, SystemEnum::WEB),
            'active_sessions_vox_ios' => $this->appSessionRepository->countActiveSessions($voxClient, SystemEnum::IOS),
            'active_sessions_vox_android' => $this->appSessionRepository->countActiveSessions($voxClient, SystemEnum::ANDROID),
            'active_push_token' => $this->appSessionRepository->countActivePushTokens(),
        ];
    }
}
