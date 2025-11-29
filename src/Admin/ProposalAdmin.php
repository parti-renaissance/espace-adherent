<?php

declare(strict_types=1);

namespace App\Admin;

use App\Entity\Proposal;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ProposalAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    /**
     * @param Proposal $object
     */
    public function getObjectMetadata(object $object): MetadataInterface
    {
        return new Metadata($object->getTitle(), $object->getDescription(), $object->getMedia()->getPath());
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $slugEditable =
            null === $this->getSubject()->getTitle()   // Creation
            || !$this->getSubject()->isPublished();     // Draft

        $form
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
                ->add('media', null, [
                    'label' => 'Image principale',
                    'required' => false,
                ])
                ->add('displayMedia', CheckboxType::class, [
                    'label' => 'Afficher l\'image principale dans la proposition',
                    'required' => false,
                ])
                ->add('themes', null, [
                    'label' => 'Thèmes',
                ])
            ->end()
            ->with('Publication', ['class' => 'col-md-4'])
                ->add('position', null, [
                    'label' => 'Ordre dans la liste',
                ])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publier la proposition',
                    'required' => false,
                ])
                ->add('slug', null, [
                    'label' => 'URL de publication',
                    'disabled' => !$slugEditable,
                    'help' => $slugEditable ? 'Ne spécifier que la fin : http://en-marche.fr/articles/[votre-valeur]<br />Doit être unique' : 'Non modifiable car publié',
                    'help_html' => true,
                ])
            ->end()
            ->with('Contenu', ['class' => 'col-md-12'])
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Nom',
            ])
            ->add('published', null, [
                'label' => 'Publié ?',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernière mise à jour',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'preview' => [
                        'template' => 'admin/proposal/list_preview.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
