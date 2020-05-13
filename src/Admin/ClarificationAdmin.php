<?php

namespace App\Admin;

use App\Entity\Clarification;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ClarificationAdmin extends AbstractAdmin
{
    use MediaSynchronisedAdminTrait;

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    /**
     * @param Clarification $object
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
            null === $this->getSubject()->getTitle()   // Creation
            || !$this->getSubject()->isPublished()     // Draft
        ;

        $formMapper
            ->with('Méta-données', ['class' => 'col-md-4'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                    'filter_emojis' => true,
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'filter_emojis' => true,
                ])
                ->add('twitterDescription', TextareaType::class, [
                    'label' => 'Description pour Twitter',
                    'filter_emojis' => true,
                    'required' => false,
                ])
                ->add('keywords', null, [
                    'label' => 'Mots clés de recherche',
                    'required' => false,
                ])
            ->end()
            ->with('Média', ['class' => 'col-md-4'])
                ->add('media', AdminType::class, [
                    'label' => 'Image principale',
                ])
                ->add('displayMedia', CheckboxType::class, [
                    'label' => 'Afficher l\'image principale',
                    'required' => false,
                ])
            ->end()
            ->with('Publication', ['class' => 'col-md-4'])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publier la désintox',
                    'required' => false,
                ])
                ->add('slug', null, [
                    'label' => 'URL de publication',
                    'disabled' => !$slugEditable,
                    'help' => $slugEditable ? 'Ne spécifier que la fin : http://en-marche.fr/emmanuel-macron/desintox/[votre-valeur]<br />Doit être unique' : 'Non modifiable car publié',
                ])
            ->end()
            ->with('Contenu', ['class' => 'col-md-12'])
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'filter_emojis' => true,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
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
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Nom',
            ])
            ->add('published', null, [
                'label' => 'Publié ?',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernière mise à jour',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'preview' => [
                        'template' => 'admin/clarification/list_preview.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
