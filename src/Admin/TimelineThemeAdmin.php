<?php

namespace AppBundle\Admin;

use AppBundle\Form\TimelineThemeMeasureType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TimelineThemeAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $slugEditable = null === $this->getSubject()->getTitle();

        $formMapper
            ->with('Méta-données', ['class' => 'col-md-8'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                    'filter_emojis' => true,
                ])
                ->add('media', null, [
                    'label' => 'Image principale',
                    'required' => false,
                ])
            ->end()
            ->with('Publication', ['class' => 'col-md-4'])
                ->add('slug', null, [
                    'label' => 'URL de publication',
                    'disabled' => !$slugEditable,
                    'help' => $slugEditable ? 'Ne spécifier que la fin : http://en-marche.fr/timeline/theme/[votre-valeur]<br />Doit être unique' : 'Non modifiable car publié',
                ])
                ->add('featured', CheckboxType::class, [
                    'label' => 'Mise en avant',
                    'required' => false,
                ])
            ->end()
            ->with('Contenu', ['class' => 'col-md-12'])
                ->add('description', TextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'filter_emojis' => true,
                ])
            ->end()
            ->with('Mesures', ['class' => 'col-md-12'])
                ->add('measures', CollectionType::class, [
                    'label' => false,
                    'required' => false,
                    'entry_type' => TimelineThemeMeasureType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Nom',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
