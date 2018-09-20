<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Mooc\AttachmentFile;
use AppBundle\Entity\Mooc\Quiz;
use AppBundle\Entity\Mooc\Video;
use AppBundle\Form\Admin\BaseFileType;
use AppBundle\Form\AttachmentLinkType;
use AppBundle\Form\PurifiedTextareaType;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class MoocElementAdmin extends AbstractAdmin
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
                ->add('chapter', ModelListType::class, [
                    'btn_add' => false,
                    'btn_edit' => false,
                    'btn_delete' => false,
                ])
                ->add('position', IntegerType::class, [
                    'label' => 'Ordre d\'affichage',
                    'required' => false,
                    'scale' => 0,
                    'attr' => [
                        'min' => 0,
                    ],
                ])
        ;
        if ($this->getSubject() instanceof Video) {
            $formMapper
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
            ;
        } elseif ($this->getSubject() instanceof Quiz) {
            $formMapper
                ->add('typeformUrl', UrlType::class, [
                    'label' => 'Lien du type form',
                ])
            ;
        }
        $formMapper
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
        ;
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
}
