<?php

namespace App\Admin;

use App\Documents\DocumentHandler;
use App\Entity\Document;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DocumentAdmin extends AbstractAdmin
{
    public function __construct(private readonly DocumentHandler $documentHandler)
    {
        parent::__construct();
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Méta-données', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('comment', TextareaType::class, [
                    'label' => 'Commentaire',
                    'required' => false,
                    'help' => 'Optionnel. Sera affiché aux utilisateurs',
                ])
            ->end()
            ->with('Contenu', ['class' => 'col-md-6'])
                ->add('file', FileType::class, [
                    'label' => false,
                    'required' => false,
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
            ->add('comment', null, [
                'label' => 'Commentaire',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    /**
     * @param Document $object
     */
    protected function prePersist(object $object): void
    {
        $this->handleFile($object);
    }

    /**
     * @param Document $object
     */
    protected function preUpdate(object $object): void
    {
        $this->handleFile($object);
    }

    private function handleFile(Document $document): void
    {
        $this->documentHandler->handleFile($document);
    }
}
