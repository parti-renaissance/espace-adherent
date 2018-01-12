<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Timeline\Profile;
use AppBundle\Form\EventListener\EmptyTranslationRemoverListener;
use AppBundle\Timeline\ProfileManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class TimelineProfileAdmin extends AbstractAdmin
{
    private $profileManager;
    private $emptyTranslationRemoverListener;

    public function __construct($code, $class, $baseControllerName, ProfileManager $profileManager, EmptyTranslationRemoverListener $emptyTranslationRemoverListener)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->profileManager = $profileManager;
        $this->emptyTranslationRemoverListener = $emptyTranslationRemoverListener;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Traductions', ['class' => 'col-md-6'])
                ->add('translations', TranslationsType::class, [
                    'by_reference' => false,
                    'label' => false,
                    'fields' => [
                        'title' => [
                            'label' => 'Titre',
                        ],
                        'slug' => [
                            'label' => 'URL de publication',
                            'sonata_help' => 'Ne spécifier que la fin : http://en-marche.fr/timeline/profil/[votre-valeur]<br />Doit être unique',
                        ],
                        'description' => [
                            'label' => 'Description',
                            'filter_emojis' => true,
                        ],
                    ],
                ])
            ->end()
        ;

        $formMapper
            ->getFormBuilder()
            ->addEventSubscriber($this->emptyTranslationRemoverListener)
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('translations.title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])

            ->add('translations.description', null, [
                'label' => 'Description',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param Profile $object
     */
    public function postPersist($object)
    {
        $this->profileManager->postPersist($object);
    }

    /**
     * @param Profile $object
     */
    public function postUpdate($object)
    {
        $this->profileManager->postUpdate($object);
    }

    /**
     * @param Profile $object
     */
    public function postRemove($object)
    {
        $this->profileManager->postRemove($object);
    }
}
