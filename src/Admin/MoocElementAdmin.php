<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Mooc\AttachmentFile;
use App\Entity\Mooc\BaseMoocElement;
use App\Entity\Mooc\Chapter;
use App\Entity\Mooc\MoocElementTypeEnum;
use App\Form\Admin\BaseFileType;
use App\Form\AttachmentLinkType;
use App\Form\ImageType;
use Doctrine\ORM\QueryBuilder;
use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class MoocElementAdmin extends AbstractAdmin implements ImageUploadAdminInterface
{
    use SortableAdminTrait;

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        /** @var QueryBuilder $proxyQuery */
        $query->addOrderBy('o.chapter', 'ASC');
        $query->addOrderBy('o.position', 'ASC');

        return $query;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->tab('Général')
                ->with('Général', ['class' => 'col-md-6'])
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                    ])
                    ->add('slug', TextType::class, [
                        'label' => 'Slug',
                        'disabled' => true,
                    ])
                    ->add('content', TextareaType::class, [
                        'label' => 'Contenu',
                    ])
                    ->add('chapter', EntityType::class, [
                        'class' => Chapter::class,
                        'placeholder' => 'Sélectionner un chapitre',
                    ])
                ->end()
                ->with('Boutons de partage', ['class' => 'col-md-6'])
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
                ->with('Liens attachés', ['class' => 'col-md-6'])
                    ->add('links', CollectionType::class, [
                        'label' => false,
                        'entry_type' => AttachmentLinkType::class,
                        'required' => false,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                    ])
                ->end()
                ->with('Fichiers attachés', ['class' => 'col-md-6'])
                    ->add('files', CollectionType::class, [
                        'label' => false,
                        'entry_type' => BaseFileType::class,
                        'entry_options' => [
                            'data_class' => AttachmentFile::class,
                        ],
                        'required' => false,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'by_reference' => false,
                    ])
                ->end()
            ->end()
        ;

        $this->addMediaTab($form);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('chapter', null, [
                'label' => 'Chapitre',
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
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('youtubeId', null, [
                'label' => 'Youtube ID',
            ])
            ->add('_thumbnail', 'thumbnail', [
                'label' => 'Miniature',
            ])
            ->add('chapter', null, [
                'label' => 'Chapitre associé',
            ])
            ->add('type', null, [
                'label' => 'Type',
                'template' => 'admin/mooc/list_type.html.twig',
            ])
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'move' => [
                        'template' => '@RunroomSortableBehavior/sort.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    public function getUploadableImagePropertyNames(?BaseMoocElement $element = null): array
    {
        if (MoocElementTypeEnum::IMAGE !== ($element ? $element->getType() : $this->getSubject()->getType())) {
            return [];
        }

        return [
            'image',
        ];
    }

    private function addMediaTab(FormMapper $formMapper): void
    {
        $formMapper->tab('Media');

        switch ($this->getSubject()->getType()) {
            case MoocElementTypeEnum::VIDEO:
                $formMapper
                    ->with('Vidéo', ['class' => 'col-md-6'])
                        ->add('youtubeId', TextType::class, [
                            'label' => 'Youtube ID',
                            'help' => 'L\'ID de la vidéo Youtube ne peut contenir que des chiffres, des lettres, et les caractères "_" et "-"',
                        ])
                        ->add('duration', TimeType::class, [
                            'label' => 'Durée de la vidéo',
                            'widget' => 'single_text',
                            'with_seconds' => true,
                        ])
                    ->end()
                ;
                break;
            case MoocElementTypeEnum::IMAGE:
                $formMapper
                    ->with('Image', ['class' => 'col-md-6'])
                        ->add('image', ImageType::class, [
                            'label' => 'Ajoutez une photo',
                            'help' => 'La photo ne doit pas dépasser 1 Mo et ne doit pas faire plus de 960x720px.',
                        ])
                    ->end()
                ;
                break;
            case MoocElementTypeEnum::QUIZ:
                $formMapper
                    ->with('Quiz', ['class' => 'col-md-6'])
                        ->add('typeformUrl', UrlType::class, [
                            'label' => 'Lien du type form',
                        ])
                    ->end()
                ;
                break;
        }

        $formMapper->end();
    }
}
