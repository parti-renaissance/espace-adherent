<?php

namespace App\Admin;

use App\Agora\AgoraMembershipHandler;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Agora;
use App\Form\Admin\SimpleMDEContent;
use App\History\UserActionHistoryHandler;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\Form\Type\CollectionType;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\Service\Attribute\Required;

class AgoraAdmin extends AbstractAdmin
{
    private ?AgoraMembershipHandler $agoraMembershipHandler = null;
    private ?UserActionHistoryHandler $historyHandler = null;
    private ?Security $security = null;

    /** @var Adherent[] */
    private array $adherentsMembersBeforeUpdate = [];

    private ?Adherent $presidentBeforeUpdate = null;
    /** @var Adherent[] */
    private array $generalSecretariesBeforeUpdate = [];

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('delete')
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('president', null, [
                'label' => 'Président',
                'template' => 'admin/agora/list_president.html.twig',
            ])
            ->add('generalSecretaries', null, [
                'label' => 'Secrétaires Généraux',
                'template' => 'admin/agora/list_general_secretaries.html.twig',
            ])
            ->add('maxMembersCount', null, [
                'label' => 'Membres',
                'template' => 'admin/agora/list_members_count.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
            ->add('published', null, [
                'label' => 'Publiée',
            ])
            ->add('createdAt', null, [
                'label' => 'Créée le',
            ])
            ->add('updatedAt', null, [
                'label' => 'Modifiée le',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('published', null, [
                'label' => 'Publiée',
                'show_filter' => true,
            ])
            ->add('president', ModelFilter::class, [
                'label' => 'Président',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'callback' => [$this, 'prepareAdherentPresidentCallback'],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getPublicId()
                        );
                    },
                ],
            ])
            ->add('generalSecretaries', ModelFilter::class, [
                'label' => 'Secrétaire général',
                'show_filter' => true,
                'field_type' => ModelAutocompleteType::class,
                'field_options' => [
                    'model_manager' => $this->getModelManager(),
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'callback' => [$this, 'prepareAdherentGeneralSecretaryCallback'],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getPublicId()
                        );
                    },
                ],
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Créée le',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('updatedAt', DateRangeFilter::class, [
                'label' => 'Modifiée le',
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Metadonnées 🧱', ['class' => 'col-md-6'])
                ->add('name', null, [
                    'label' => 'Nom',
                    'required' => true,
                ])
                ->add('slug', null, [
                    'label' => 'Slug',
                    'disabled' => true,
                ])
                ->add('description', SimpleMDEContent::class, [
                    'label' => 'Description',
                    'required' => false,
                    'attr' => ['rows' => 10],
                    'help_html' => true,
                ])
            ->end()
            ->with('Accès ⚙️', ['class' => 'col-md-6'])
                ->add('maxMembersCount', null, [
                    'label' => 'Nombre maximum de membres',
                ])
                ->add('published', null, [
                    'label' => 'Publiée',
                ])
            ->end()
            ->with('Privilèges 🗝️', ['class' => 'col-md-6'])
                ->add('president', ModelAutocompleteType::class, [
                    'label' => 'Président',
                    'required' => true,
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getPublicId()
                        );
                    },
                    'btn_add' => false,
                ])
                ->add('generalSecretaries', ModelAutocompleteType::class, [
                    'label' => 'Secrétaire généraux',
                    'multiple' => true,
                    'required' => false,
                    'minimum_input_length' => 1,
                    'items_per_page' => 20,
                    'property' => [
                        'search',
                    ],
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getPublicId()
                        );
                    },
                    'btn_add' => false,
                ])
            ->end()
            ->with('Membres 👥', ['class' => 'col-md-12'])
                ->add('memberships', CollectionType::class, [
                    'label' => false,
                    'by_reference' => false,
                    'required' => false,
                    'error_bubbling' => false,
                ], [
                    'edit' => 'inline',
                    'inline' => 'table',
                ])
            ->end()
        ;
    }

    protected function alterObject(object $object): void
    {
        if (!$object instanceof Agora) {
            return;
        }

        $this->adherentsMembersBeforeUpdate = [];

        foreach ($object->memberships as $membership) {
            if ($membership->adherent) {
                $this->adherentsMembersBeforeUpdate[$membership->adherent->getId()] = $membership->adherent;
            }
        }

        $this->presidentBeforeUpdate = $object->president;

        $this->generalSecretariesBeforeUpdate = [];
        foreach ($object->generalSecretaries as $adherent) {
            $this->generalSecretariesBeforeUpdate[$adherent->getId()] = $adherent;
        }
    }

    protected function postPersist(object $object): void
    {
        if (!$object instanceof Agora) {
            return;
        }

        foreach ($object->memberships as $membership) {
            if ($membership->adherent) {
                $this->historyHandler->createAgoraMembershipAdd($membership->adherent, $object, $this->getAdministrator());
            }
        }

        if ($object->president) {
            $this->historyHandler->createAgoraPresidentAdd($object->president, $object, $this->getAdministrator());
        }

        foreach ($object->generalSecretaries as $generalSecretary) {
            $this->historyHandler->createAgoraGeneralSecretaryAdd($generalSecretary, $object, $this->getAdministrator());
        }

        $this->handleManagersAsMembers($object);
    }

    protected function postUpdate(object $object): void
    {
        if (!$object instanceof Agora) {
            return;
        }

        $after = [];
        foreach ($object->memberships as $membership) {
            if ($membership->adherent) {
                $after[$membership->adherent->getId()] = $membership->adherent;
            }
        }

        // Detect removed membership
        foreach ($this->adherentsMembersBeforeUpdate as $id => $adherent) {
            if (!isset($after[$id])) {
                $this->historyHandler->createAgoraMembershipRemove($adherent, $object, $this->getAdministrator());
            }
        }

        // Detect added membership
        foreach ($after as $id => $adherent) {
            if (!isset($this->adherentsMembersBeforeUpdate[$id])) {
                $this->historyHandler->createAgoraMembershipAdd($adherent, $object, $this->getAdministrator());
            }
        }

        // Detect president changes
        $newPresident = $object->president;
        if ($this->presidentBeforeUpdate !== $newPresident) {
            if (null !== $this->presidentBeforeUpdate) {
                $this->historyHandler->createAgoraPresidentRemove($this->presidentBeforeUpdate, $object, $this->getAdministrator());
            }
            if (null !== $newPresident) {
                $this->historyHandler->createAgoraPresidentAdd($newPresident, $object, $this->getAdministrator());
            }
        }

        // Detect general secretaries changes
        $afterGeneralSecretaries = [];
        foreach ($object->generalSecretaries as $adherent) {
            $afterGeneralSecretaries[$adherent->getId()] = $adherent;
        }

        foreach ($this->generalSecretariesBeforeUpdate as $id => $adherent) {
            if (!isset($afterGeneralSecretaries[$id])) {
                $this->historyHandler->createAgoraGeneralSecretaryRemove($adherent, $object, $this->getAdministrator());
            }
        }

        foreach ($afterGeneralSecretaries as $id => $adherent) {
            if (!isset($this->generalSecretariesBeforeUpdate[$id])) {
                $this->historyHandler->createAgoraGeneralSecretaryAdd($adherent, $object, $this->getAdministrator());
            }
        }

        $this->handleManagersAsMembers($object);
    }

    private function handleManagersAsMembers(Agora $agora): void
    {
        $president = $agora->president;
        if ($president && !$this->agoraMembershipHandler->isMember($president, $agora)) {
            $this->agoraMembershipHandler->add($president, $agora);
        }

        foreach ($agora->generalSecretaries as $generalSecretary) {
            if (!$this->agoraMembershipHandler->isMember($generalSecretary, $agora)) {
                $this->agoraMembershipHandler->add($generalSecretary, $agora);
            }
        }
    }

    public function prepareAdherentPresidentCallback(AbstractAdmin $admin): void
    {
        /** @var QueryBuilder $qb */
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $qb->innerJoin("$alias.presidentOfAgoras", 'a');
    }

    public function prepareAdherentGeneralSecretaryCallback(AbstractAdmin $admin): void
    {
        /** @var QueryBuilder $qb */
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $qb->innerJoin("$alias.generalSecretaryOfAgoras", 'a');
    }

    #[Required]
    public function setAgoraMembershipHandler(AgoraMembershipHandler $agoraMembershipHandler): void
    {
        $this->agoraMembershipHandler = $agoraMembershipHandler;
    }

    #[Required]
    public function setUserActionHistoryHandler(UserActionHistoryHandler $historyHandler): void
    {
        $this->historyHandler = $historyHandler;
    }

    #[Required]
    public function setSecurity(Security $security): void
    {
        $this->security = $security;
    }

    private function getAdministrator(): Administrator
    {
        return $this->security->getUser();
    }
}
