<?php

namespace App\Admin\Article;

use App\Admin\AbstractAdmin;
use App\Entity\Article;
use App\Form\Admin\UnlayerContentType;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Sonata\Form\Type\DatePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

abstract class AbstractArticleAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'publishedAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    /**
     * @param Article $object
     */
    public function getObjectMetadata(object $object): MetadataInterface
    {
        return new Metadata($object->getTitle(), $object->getDescription(), $object->getMedia()->getPath());
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->tab('Contenu')
                ->with('')
                    ->add('jsonContent', HiddenType::class)
                    ->add('content', UnlayerContentType::class, ['label' => false])
                ->end()
            ->end()
            ->tab('Méta-données')
                ->with('Méta-données', ['class' => 'col-md-8'])
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                    ])
                    ->add('description', TextareaType::class, [
                        'label' => 'Description',
                    ])
                    ->add('twitterDescription', TextareaType::class, [
                        'label' => 'Description pour Twitter',
                        'required' => false,
                    ])
                    ->add('keywords', null, [
                        'label' => 'Mots clés de recherche',
                        'required' => false,
                    ])
                    ->add('media', ModelType::class, [
                        'label' => 'Image principale',
                        'required' => false,
                        'btn_add' => 'Créer',
                    ])
                    ->add('displayMedia', CheckboxType::class, [
                        'label' => 'Afficher l\'image principale dans l\'article',
                        'required' => false,
                    ])
                    ->add('themes', null, [
                        'label' => 'Thèmes',
                    ])
                ->end()
                ->with('Publication', ['class' => 'col-md-4'])
                    ->add('published', CheckboxType::class, [
                        'label' => 'Publier l\'article',
                        'required' => false,
                    ])
                    ->add('publishedAt', DatePickerType::class, [
                        'label' => 'Date de publication',
                    ])
                    ->add('slug', null, [
                        'label' => 'URL de publication',
                        'help' => 'Ne spécifier que la fin : http://en-marche.fr/articles/[votre-valeur]<br />Doit être unique',
                        'help_html' => true,
                    ])
                    ->add('category', null, [
                        'label' => 'Catégorie de publication',
                    ])
                ->end()
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Nom',
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
            ])
            ->add('published', null, [
                'label' => 'Publié ?',
            ])
            ->add('publishedAt', null, [
                'label' => 'Date de publication',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernière mise à jour',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'preview' => [
                        'template' => 'admin/article/list_preview.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query
            ->andWhere(sprintf('%s.forRenaissance = :forRenaissance', $query->getRootAliases()[0]))
            ->setParameter('forRenaissance', $this->isRenaissanceArticle())
        ;

        return $query;
    }

    protected function isRenaissanceArticle(): bool
    {
        return false;
    }
}
