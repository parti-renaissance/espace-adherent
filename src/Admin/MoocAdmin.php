<?php

namespace App\Admin;

use App\Entity\Mooc\Chapter;
use App\Form\ImageType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class MoocAdmin extends AbstractAdmin implements ImageUploadAdminInterface
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('Général')
                ->with('Général', ['class' => 'col-md-8'])
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                    ])
                    ->add('description', TextType::class, [
                        'label' => 'Description',
                    ])
                    ->add('slug', TextType::class, [
                        'label' => 'Slug',
                        'disabled' => true,
                    ])
                    ->add('content', TextareaType::class, [
                        'label' => 'Contenu',
                    ])
                ->end()
                ->with('Boutons de partage', ['class' => 'col-md-4'])
                    ->add('shareTwitterText', TextType::class, [
                        'label' => 'Texte du partage sur Twitter',
                    ])
                    ->add('shareFacebookText', TextType::class, [
                        'label' => 'Texte du partage sur Facebook',
                    ])
                    ->add('shareEmailSubject', TextType::class, [
                        'label' => 'Sujet de l\'email de partage',
                    ])
                    ->add('shareEmailBody', TextareaType::class, [
                        'label' => 'Corps de l\'email de partage',
                        'attr' => ['rows' => 5, 'maxlength' => 500],
                    ])
                ->end()
        ;

        if (!$this->getRequest()->isXmlHttpRequest()) {
            $form
                ->with('Chapitres', ['class' => 'col-md-4'])
                    ->add('chapters', EntityType::class, [
                        'class' => Chapter::class,
                        'by_reference' => false,
                        'label' => 'Chapitre',
                        'multiple' => true,
                    ])
                ->end()
            ;
        }

        $form
            ->end()
            ->tab('Media')
                ->with('Liste', ['class' => 'col-md-6'])
                    ->add('listImage', ImageType::class, [
                        'required' => false,
                        'allow_delete' => !empty($this->getSubject()->getListImage()),
                        'label' => 'Ajoutez une photo',
                        'help' => 'La photo ne doit pas dépasser 1 Mo et ne doit pas faire plus de 960x720px.',
                    ])
                ->end()
                ->with('Article', ['class' => 'col-md-6'])
                    ->add('articleImage', ImageType::class, [
                        'required' => false,
                        'allow_delete' => !empty($this->getSubject()->getArticleImage()),
                        'label' => 'Ajoutez une photo',
                        'help' => 'La photo ne doit pas dépasser 1 Mo et ne doit pas faire plus de 960x720px.',
                    ])
                    ->add('youtubeId', TextType::class, [
                        'label' => 'Youtube ID',
                        'help' => 'L\'ID de la vidéo Youtube ne peut contenir que des chiffres, des lettres, et les caractères "_" et "-"',
                        'required' => false,
                    ])
                    ->add('youtubeDuration', TimeType::class, [
                        'label' => 'Durée de la vidéo',
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'required' => false,
                    ])
                ->end()
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
            ->add('description', null, [
                'label' => 'Description',
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
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('youtubeId', null, [
                'label' => 'Youtube ID',
                'template' => 'admin/list/list_youtube_id.html.twig',
            ])
            ->add('_thumbnail', null, [
                'label' => 'Miniature',
                'virtual_field' => true,
                'template' => 'admin/list/list_mooc_thumbnail.html.twig',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    public function getUploadableImagePropertyNames(): array
    {
        return [
            'articleImage',
            'listImage',
        ];
    }
}
