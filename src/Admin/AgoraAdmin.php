<?php

namespace App\Admin;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Agora;
use App\Form\Admin\SimpleMDEContent;
use App\History\UserActionHistoryHandler;
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
            ->add('published', null, [
                'label' => 'Publiée',
            ])
            ->add('maxMembersCount', null, [
                'label' => 'Places',
            ])
            ->add('president', null, [
                'label' => 'Président',
                'template' => 'admin/agora/list_president.html.twig',
            ])
            ->add('generalSecretaries', null, [
                'label' => 'Secrétaires Généraux',
                'template' => 'admin/agora/list_general_secretaries.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Créée le',
            ])
            ->add('updatedAt', null, [
                'label' => 'Modifiée le',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
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
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getId()
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
                    'to_string_callback' => static function (Adherent $adherent): string {
                        return \sprintf(
                            '%s (%s) [%s]',
                            $adherent->getFullName(),
                            $adherent->getEmailAddress(),
                            $adherent->getId()
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
                            $adherent->getId()
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
                            $adherent->getId()
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
    }

    #[Required]
    public function setContactHandler(UserActionHistoryHandler $historyHandler): void
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
