<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\{
    Admin\AbstractAdmin, Datagrid\ListMapper, Datagrid\DatagridMapper, Form\FormMapper
};
use Symfony\Component\Form\Extension\Core\Type\{TextareaType, TextType};

class PageAdmin extends AbstractAdmin
{
    use AmpSynchronisedAdminTrait;

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 64,
        '_sort_order' => 'ASC',
        '_sort_by' => 'title',
    ];

    protected function configureFormFields(FormMapper $formMapper)
        : void
    {
        if ($this->getSubject()->getId() === null) {
            $formMapper->add('title', TextType::class, [
                'label' => 'Titre',
                'filter_emojis' => true,
            ]);
        }

        $formMapper
            ->with('Contenu', ['class' => 'col-md-8'])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'filter_emojis' => true,
                ])
                ->add('twitterDescription', TextareaType::class, [
                    'label' => 'Description pour Twitter',
                    'filter_emojis' => true,
                    'required' => false,
                ])
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
                    'filter_emojis' => true,
                    'attr' => ['class' => 'content-editor', 'rows' => 20],
                ])
            ->end()
            ->with('Méta-donnes', ['class' => 'col-md-4'])
                ->add('keywords', null, [
                    'label' => 'Mots clés de recherche',
                    'required' => false,
                ])
                ->add('media', null, [
                    'label' => 'Image principale',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
        : void
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
        : void
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de dernière mise à jour',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }
}
