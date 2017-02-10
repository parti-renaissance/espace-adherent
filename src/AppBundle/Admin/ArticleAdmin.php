<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Article;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ArticleAdmin extends AbstractAdmin
{
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
            ->with('Méta-données', array('class' => 'col-md-8'))
                ->add('title', null, [
                    'label' => 'Titre',
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
                ->add('themes', null, [
                    'label' => 'Thèmes',
                ])
            ->end()
            ->with('Publication', array('class' => 'col-md-4'))
                ->add('published', CheckboxType::class, [
                    'label' => 'Publier l\'article',
                    'required' => false,
                ])
                ->add('publishedAt', 'sonata_type_date_picker', [
                    'label' => 'Date de publication',
                ])
                ->add('slug', null, [
                    'label' => 'URL de publication',
                    'disabled' => !$slugEditable,
                    'help' => $slugEditable ? 'Ne spécifier que la fin : http://en-marche.fr/article/[votre-valeur]<br />Doit être unique' : 'Non modifiable car publié',
                ])
                ->add('category', null, [
                    'label' => 'Catégorie de publication',
                ])
            ->end()
            ->with('Contenu', array('class' => 'col-md-12'))
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
            ->end();
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
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
            ->add('publishedAt', null, [
                'label' => 'Date de publication',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernière mise à jour',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
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
