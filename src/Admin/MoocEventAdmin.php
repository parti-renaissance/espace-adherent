<?php

namespace AppBundle\Admin;

use AppBundle\Entity\MoocEvent;
use AppBundle\Form\MoocEventCategoryType;
use AppBundle\MoocEvent\MoocEventManager;
use AppBundle\Form\UnitedNationsCountryType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MoocEventAdmin extends AbstractAdmin
{
    /**
     * @var MoocEventManager
     */
    private $manager;

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'ASC',
        '_sort_by' => 'name',
    ];

    public function setMoocEventManager(MoocEventManager $manager): MoocEventAdmin
    {
        $this->manager = $manager;

        return $this;
    }

    public function getTemplate($name)
    {
        if ('show' === $name) {
            return 'admin/mooc_event/show.html.twig';
        }

        if ('edit' === $name) {
            return 'admin/mooc_event/edit.html.twig';
        }

        return parent::getTemplate($name);
    }

    public function postUpdate($object)
    {
        $this->manager->checkPublicationMoocEvent($object);
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->with('Événements MOOC', array('class' => 'col-md-7'))
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('category', null, [
                    'label' => 'Catégorie',
                ])
                ->add('group', null, [
                    'label' => 'Groupe organisateur',
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
                    'choices' => array_combine(MoocEvent::STATUSES, MoocEvent::STATUSES),
                    'catalogue' => 'forms',
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
            ->with('Événements MOOC', array('class' => 'col-md-7'))
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('category', MoocEventCategoryType::class, [
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
                    'choices' => array_combine(MoocEvent::STATUSES, MoocEvent::STATUSES),
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
                    'label' => 'description',
                    'required' => false,
                    'filter_emojis' => true,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('group', null, [
                'label' => 'Groupe organisateur',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('organizer', null, [
                'label' => 'Organisateur',
                'template' => 'admin/mooc_event/list_organizer.html.twig',
            ])
            ->add('beginAt', null, [
                'label' => 'Date de début',
            ])
            ->add('postAddress', null, [
                'label' => 'Lieu',
                'virtual_field' => true,
                'template' => 'admin/mooc_event/list_location.html.twig',
            ])
            ->add('finishAt', null, [
                'label' => 'Date de fin',
            ])
            ->add('participantsCount', null, [
                'label' => 'Nombre de participants',
            ])
            ->add('status', null, [
                'label' => 'Statut',
                'template' => 'admin/mooc_event/list_status.html.twig',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/mooc_event/list_actions.html.twig',
            ]);
    }
}
