<?php

namespace App\Admin;

use App\Entity\Mooc\AttachmentFile;
use App\Entity\Mooc\BaseMoocElement;
use App\Entity\Mooc\Chapter;
use App\Entity\Mooc\MoocElementTypeEnum;
use App\Form\Admin\BaseFileType;
use App\Form\AttachmentLinkType;
use App\Form\ImageType;
use App\Form\PurifiedTextareaType;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class MoocElementAdmin extends AbstractAdmin implements ImageUploadAdminInterface
{
    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = parent::createQuery($context);
        $proxyQuery->addOrderBy('o.chapter', 'ASC');
        $proxyQuery->addOrderBy('o.position', 'ASC');

        return $proxyQuery;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Général')
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
                        'attr' => ['class' => 'ck-editor-advanced'],
                        'purifier_type' => 'enrich_content',
                        'filter_emojis' => true,
                    ])
                    ->add('chapter', EntityType::class, [
                        'class' => Chapter::class,
                        'placeholder' => 'Sélectionner un chapitre',
                    ])
                    ->add('position', IntegerType::class, [
                        'label' => 'Ordre d\'affichage',
                        'required' => false,
                        'scale' => 0,
                        'attr' => [
                            'min' => 0,
                        ],
                    ])
                ->end()
                ->with('Boutons de partage', ['class' => 'col-md-6'])
                    ->add('shareTwitterText', TextType::class, [
                        'label' => 'Texte du partage sur Twitter',
                        'filter_emojis' => true,
                    ])
                    ->add('shareFacebookText', TextType::class, [
                        'label' => 'Texte du partage sur Facebook',
                        'filter_emojis' => true,
                    ])
                    ->add('shareEmailSubject', TextType::class, [
                        'label' => 'Sujet de l\'email de partage',
                        'filter_emojis' => true,
                    ])
                    ->add('shareEmailBody', PurifiedTextareaType::class, [
                        'label' => 'Corps de l\'email de partage',
                        'attr' => ['rows' => 5, 'maxlength' => 500],
                        'purifier_type' => 'enrich_content',
                        'filter_emojis' => true,
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

        $this->addMediaTab($formMapper);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
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
            ->add('_action', null, [
                'actions' => [
                    'move' => [
                        'template' => '@PixSortableBehavior/Default/_sort_drag_drop.html.twig',
                        'enable_top_bottom_buttons' => true,
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
        $collection->add('move', $this->getRouterIdParameter().'/move/{position}');
    }

    public function getUploadableImagePropertyNames(BaseMoocElement $element = null): array
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
                            'filter_emojis' => true,
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
