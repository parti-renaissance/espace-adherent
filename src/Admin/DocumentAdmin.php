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
    private DocumentHandler $documentHandler;

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
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

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
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
    protected function postPersist(object $object): void
    {
        $this->handleFile($object);
    }

    /**
     * @param Document $object
     */
    protected function postUpdate(object $object): void
    {
        $this->handleFile($object);
    }

    private function handleFile(Document $document): void
    {
        $this->documentHandler->handleFile($document);
    }

    /**
     * @required
     */
    public function setDocumentHandler(DocumentHandler $documentHandler): void
    {
        $this->documentHandler = $documentHandler;
    }
}
