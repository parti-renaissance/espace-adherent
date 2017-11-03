<?php

namespace AppBundle\Admin;

use AppBundle\CitizenInitiative\CitizenInitiativeManager;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Form\CitizenInitiativeCategoryType;
use AppBundle\Form\UnitedNationsCountryType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CitizenInitiativeAdmin extends AbstractAdmin
{
    /**
     * @var CitizenInitiativeManager
     */
    private $manager;

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    public function setCitizenInitiativeManager(CitizenInitiativeManager $manager): self
    {
        $this->manager = $manager;

        return $this;
    }

    public function getTemplate($name)
    {
        if ('show' === $name) {
            return 'admin/citizen_initiative/show.html.twig';
        }

        if ('edit' === $name) {
            return 'admin/citizen_initiative/edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    public function postUpdate($object)
    {
        $this->manager->checkPublicationCitizenInitiative($object);
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->with('Initiative citoyenne', array('class' => 'col-md-7'))
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('organizer', null, [
                'label' => 'Organisateur',
            ])
            ->add('description', null, [
                'label' => 'Description',
                'attr' => [
                    'rows' => '3',
                ],
                'safe' => true,
            ])
            ->add('beginAt', null, [
                'label' => 'Date de début',
            ])
            ->add('finishAt', null, [
                'label' => 'Date de fin',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('participantsCount', null, [
                'label' => 'Nombre de participants',
            ])
            ->add('status', 'choice', [
                'label' => 'Statut',
                'choices' => CitizenInitiative::STATUSES,
                'catalogue' => 'forms',
            ])
            ->add('expertAssistanceNeeded', null, [
                'label' => 'Demande d\'expert ?',
            ])
            ->add('coachingRequested', null, [
                'label' => 'Demande d\'accomp.',
            ])
            ->end()
            ->with('Organisateur', array('class' => 'col-md-5'))
            ->add('organizer.fullName', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('organizer.emailAddress', TextType::class, [
                'label' => 'Adresse E-mail',
            ])
            ->add('organizer.phone', null, [
                'label' => 'Téléphone',
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
            ->end();
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Initiative citoyenne', array('class' => 'col-md-7'))
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('category', CitizenInitiativeCategoryType::class, [
                    'label' => 'Catégorie',
                ])
                ->add('beginAt', null, [
                    'label' => 'Date de début',
                ])
                ->add('finishAt', null, [
                    'label' => 'Date de fin',
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'Statut',
                    'choices' => CitizenInitiative::STATUSES,
                    'choice_translation_domain' => 'forms',
                ])
                ->add('published', null, [
                    'label' => 'Publié',
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
            ->with('Description', ['class' => 'col-md-12'])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                    'filter_emojis' => true,
                    'attr' => ['class' => 'ck-editor'],
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('organizer', null, [
                'label' => 'Organisateur',
                'template' => 'admin/citizen_initiative/list_organizer.html.twig',
            ])
            ->add('beginAt', null, [
                'label' => 'Date de début',
            ])
            ->add('postAddress', null, [
                'label' => 'Lieu',
                'virtual_field' => true,
                'template' => 'admin/citizen_initiative/list_location.html.twig',
            ])
            ->add('finishAt', null, [
                'label' => 'Date de fin',
            ])
            ->add('expertAssistanceNeeded', null, [
                'label' => 'Demande d\'expert ?',
            ])
            ->add('coachingRequested', null, [
                'label' => 'Demande d\'accomp.',
            ])
            ->add('participantsCount', null, [
                'label' => 'Nombre de participants',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/citizen_initiative/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/citizen_initiative/list_actions.html.twig',
            ]);
    }
}
