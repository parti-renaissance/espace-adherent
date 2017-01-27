<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Article;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleAdmin extends AbstractAdmin
{
    use CloudflareSynchronizedAdminTrait;

    /**
     * @param Article $object
     */
    public function invalidate($object)
    {
        $this->getCloudflare()->invalidateTag('article-'.$object->getId());
    }

    /**
     * @param Article $object
     *
     * @return Metadata
     */
    public function getObjectMetadata($object)
    {
        return new Metadata($object->getTitle(), $object->getDescription(), $object->getMedia()->getPath());
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $slugEditable =
            $this->getSubject()->getTitle() === null   // Creation
            || !$this->getSubject()->isPublished()     // Draft
        ;

        $formMapper
            ->add('published', CheckboxType::class, [
                'label' => 'Publier l\'article',
                'required' => false,
            ])
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('slug', null, [
                'label' => $slugEditable ? 'URL (ne spécifier que la fin : http://en-marche.fr/article/<votre-valeur>, doit être unique)' : 'URL (non modifiable)',
                'disabled' => !$slugEditable,
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => [
                    'Actualité' => Article::CATEGORY_ACTUALITE,
                    'Vidéo' => Article::CATEGORY_VIDEO,
                    'Photos' => Article::CATEGORY_PHOTOS,
                    'Discours' => Article::CATEGORY_DISCOURS,
                    'Média' => Article::CATEGORY_MEDIA,
                    'Communiqué' => Article::CATEGORY_COMMUNIQUE,
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('media', null, [
                'label' => 'Image principale',
                'required' => false,
            ])
            ->add('displayMedia', CheckboxType::class, [
                'label' => 'Afficher l\'image principale dans l\'article',
                'required' => false,
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                'required' => false,
                'attr' => ['class' => 'content-editor', 'rows' => 20],
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('category', null, [
                'label' => 'Catégorie',
                'show_filter' => true,
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
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
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de dernière mise à jour',
            ])
            ->add('_action', null, [
                'actions' => [
                    'preview' => [
                        'template' => 'admin/article_preview.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
