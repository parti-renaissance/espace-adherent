<?php

namespace App\Admin;

use App\Entity\Page;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PageAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'title';
        $sortValues[DatagridInterface::PER_PAGE] = 64;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        if (null === $this->getSubject()->getId()) {
            $form
                ->with('Title', ['class' => 'col-md-12'])
                ->add('slug', TextType::class, [
                    'label' => 'URL de publication',
                    'help' => 'Ne spécifier que la fin : http://en-marche.fr/[votre-valeur]<br />Doit être unique',
                    'help_html' => true,
                ])
                ->end()
            ;
        }

        $form
            ->with('Title', ['class' => 'col-md-12'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                    'help' => 'Le titre ne sera visible que sur les layouts qui affichent le titre',
                ])
            ->end()
            ->with('Contenu', ['class' => 'col-md-8'])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                ])
                ->add('twitterDescription', TextareaType::class, [
                    'label' => 'Description pour Twitter',
                    'required' => false,
                ])
                ->add('layout', ChoiceType::class, [
                    'label' => 'Layout',
                    'choices' => Page::LAYOUTS,
                    'choice_label' => function (?string $choice) {
                        return "page.layout.$choice";
                    },
                ])
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
            ->end()
            ->with('Méta-données', ['class' => 'col-md-4'])
                ->add('keywords', null, [
                    'label' => 'Mots clés de recherche',
                    'required' => false,
                ])
                ->add('media', null, [
                    'label' => 'Image principale',
                    'required' => false,
                ])
            ->add('headerMedia', null, [
                'label' => 'Image d\'en-tête',
                'required' => false,
                'help' => 'Cette image ne sera visible que sur les layouts qui affichent l\'image d\'en-tête. Largeur min. recommandée: 1920px',
            ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('slug', null, [
                'label' => 'Slug',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de dernière mise à jour',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
