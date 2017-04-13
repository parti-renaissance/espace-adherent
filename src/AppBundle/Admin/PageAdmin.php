<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class PageAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 64,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        if ($this->getSubject()->getId() === null) {
            $formMapper->add('title', null, [
                'label' => 'Titre',
            ]);
        }

        $formMapper
            ->with('Contenu', ['class' => 'col-md-8'])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                ])
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
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
