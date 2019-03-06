<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Mooc\Chapter;
use AppBundle\Entity\Mooc\Mooc;
use AppBundle\Form\PurifiedTextareaType;
use AppBundle\Mooc\MoocManager;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class MoocAdmin extends AbstractAdmin
{
    /**
     * @var MoocManager
     */
    private $moocManager;

    public function __construct(string $code, string $class, string $baseControllerName, MoocManager $moocManager)
    {
        parent::__construct($code, $class, $baseControllerName);

        $this->moocManager = $moocManager;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('MOOC')
                ->with('Général', ['class' => 'col-md-6'])
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                        'filter_emojis' => true,
                    ])
                    ->add('image', FileType::class, [
                        'required' => false,
                        'label' => 'Ajoutez une photo',
                        'help' => 'La photo ne doit pas dépasser 1 Mo et ne doit pas faire plus de 960x720px.',
                    ])
                    ->add('description', TextType::class, [
                        'label' => 'Description',
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
                        'filter_emojis' => true,
                    ])
                    ->add('youtubeId', TextType::class, [
                        'label' => 'Youtube ID',
                        'help' => 'L\'ID de la vidéo Youtube ne peut contenir que des chiffres, des lettres, et les caractères "_" et "-"',
                        'filter_emojis' => true,
                    ])
                    ->add('youtubeDuration', TimeType::class, [
                        'label' => 'Durée de la vidéo',
                        'widget' => 'single_text',
                        'with_seconds' => true,
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
        ;

        if (!$this->request->isXmlHttpRequest()) {
            $formMapper
                ->with('Chapitres', ['class' => 'col-md-6'])
                    ->add('chapters', EntityType::class, [
                        'class' => Chapter::class,
                        'by_reference' => false,
                        'label' => 'Chapitre',
                        'multiple' => true,
                    ])
                ->end()
            ;
        }

        $formMapper->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
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

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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
            ->add('_thumbnail', 'thumbnail', [
                'label' => 'Miniature',
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

    /**
     * @param Mooc $mooc
     */
    public function postRemove($mooc)
    {
        parent::postRemove($mooc);

        $this->moocManager->removeImage($mooc);
    }

    /**
     * @param Mooc $mooc
     */
    public function prePersist($mooc)
    {
        parent::prePersist($mooc);

        if ($mooc->getImage()) {
            $this->moocManager->saveImage($mooc);
        }
    }

    /**
     * @param Mooc $mooc
     */
    public function preUpdate($mooc)
    {
        parent::preUpdate($mooc);

        if ($mooc->getImage()) {
            $this->moocManager->saveImage($mooc);
        }
    }
}
