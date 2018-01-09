<?php

namespace AppBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use AppBundle\Entity\Timeline\Profile;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class TimelineThemeAdmin extends AbstractAdmin
{
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery();
        $alias = $query->getRootAlias();

        $query
            ->leftJoin("$alias.translations", 'translations')
            ->addSelect('translations')
        ;

        return $query;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Traductions', ['class' => 'col-md-6'])
                ->add('translations', TranslationsType::class, [
                    'by_reference' => false,
                    'label' => false,
                    'default_locale' => [Profile::DEFAULT_LOCALE],
                    'locales' => Profile::LOCALES_TO_INDEX,
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
            ->with('Publication', ['class' => 'col-md-4'])
                ->add('featured', CheckboxType::class, [
                    'label' => 'Mise en avant',
                    'required' => false,
                ])
                ->add('media', null, [
                    'label' => 'Image principale',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('translations.title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('featured', null, [
                'label' => 'Mise en avant',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_thumbnail', null, [
                'label' => 'Image',
                'virtual_field' => true,
                'template' => 'admin/timeline/theme/list_image.html.twig',
            ])
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('featured', null, [
                'label' => 'Mise en avant',
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
}
