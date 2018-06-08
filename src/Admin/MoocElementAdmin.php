<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Mooc\Quizz;
use AppBundle\Entity\Mooc\Video;
use AppBundle\Form\AttachmentFileType;
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
use Sonata\DatagridBundle\ProxyQuery\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;

class MoocElementAdmin extends AbstractAdmin
{
    public function createQuery($context = 'list')
    {
        /** @var ProxyQueryInterface|QueryBuilder $proxyQuery */
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
                    'attr' => ['class' => 'ck-editor'],
                    'purifier_type' => 'enrich_content',
                ])
                ->add('chapter', ModelListType::class)
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
                    'label' => 'ID de la vidéo Youtube',
                    'help' => 'L\'ID ne peut contenir que des chiffres, des lettres, ou les caractères "_" et "-"',
                    'filter_emojis' => true,
                ])
                ->add('duration', TimeType::class, [
                    'label' => 'Durée de la vidéo Youtube',
                    'widget' => 'single_text',
                    'with_seconds' => true,
                ])
            ;
        } elseif ($this->getSubject() instanceof Quizz) {
            $formMapper
                ->add('typeForm', TextareaType::class, [
                    'label' => 'Contenu du type form',
                ])
            ;
        }
        $formMapper
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
                    'entry_type' => AttachmentFileType::class,
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
                'label' => 'ID de la vidéo Youtube',
            ])
            ->add('_thumbnail', 'thumbnail', [
                'label' => 'Miniature Youtube',
            ])
            ->add('chapter', null, [
                'label' => 'Chapitre associé',
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
