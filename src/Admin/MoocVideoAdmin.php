<?php

namespace AppBundle\Admin;

use AppBundle\Form\Mooc\AttachmentLinkType;
use AppBundle\Form\PurifiedTextareaType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MoocVideoAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Vidéos')
                ->with('Général', ['class' => 'col-md-6'])
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                        'filter_emojis' => true,
                    ])
                    ->add('slug', TextType::class, [
                        'label' => 'Slug',
                        'disabled' => true,
                    ])
                    ->add('content', PurifiedTextareaType::class, [
                        'label' => 'Contenu',
                        'attr' => ['class' => 'ck-editor'],
                        'purifier_type' => 'enrich_content',
                    ])
                    ->add('youtubeId', TextType::class, [
                        'label' => 'ID de la vidéo Youtube',
                        'help' => 'L\'ID ne peut contenir que des chiffres, des lettres, ou les caractères "_" et "-"',
                        'filter_emojis' => true,
                    ])
                    ->add('displayOrder', IntegerType::class, [
                        'label' => 'Ordre d\'affichage',
                        'required' => false,
                        'scale' => 0,
                        'attr' => [
                            'min' => 0,
                        ],
                    ])
                ->end()
                ->with('Liens attachés', ['class' => 'col-md-6'])
                    ->add('attachmentLinks', CollectionType::class, [
                        'label' => false,
                        'entry_type' => AttachmentLinkType::class,
                        'required' => false,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                    ])
                ->end()
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
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
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('youtubeId', null, [
                'label' => 'ID de la vidéo Youtube',
            ])
            ->add('_thumbnail', 'thumbnail', [
                'label' => 'Miniature Youtube',
            ])
            ->add('chapter', null, [
                'label' => 'Chapitre associé',
            ])
            ->add('displayOrder', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }
}
