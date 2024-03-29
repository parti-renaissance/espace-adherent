<?php

namespace App\Admin\NationalEvent;

use App\Admin\AbstractAdmin;
use App\Entity\NationalEvent\NationalEvent;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use League\Flysystem\FilesystemInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Contracts\Service\Attribute\Required;

class NationalEventAdmin extends AbstractAdmin
{
    private FilesystemInterface $storage;

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => 'Id'])
            ->addIdentifier('name', null, ['label' => 'Nom'])
            ->add('slug', null, ['label' => 'Lien', 'template' => 'admin/national_event/list_slug.html.twig'])
            ->add('intoImagePath', null, ['label' => 'Image', 'template' => 'admin/national_event/list_image.html.twig'])
            ->add('startDate', null, ['label' => 'Date de début'])
            ->add('endDate', null, ['label' => 'Date de fin'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['edit' => []]])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('name', null, ['label' => 'Nom'])
                ->add('source', null, ['label' => 'Source', 'help' => 'UTM source pour s\'inscrire même après la fermeture des inscriptions'])
                ->add('startDate', null, ['label' => 'Date de début', 'widget' => 'single_text', 'with_seconds' => true])
                ->add('endDate', null, ['label' => 'Date de fin', 'widget' => 'single_text', 'with_seconds' => true])
                ->add('ticketStartDate', null, ['label' => 'Billetterie - date de début', 'widget' => 'single_text', 'with_seconds' => true])
                ->add('ticketEndDate', null, ['label' => 'Billetterie - date de fin', 'widget' => 'single_text', 'with_seconds' => true])
            ->end()
            ->with('Image', ['class' => 'col-md-6'])
                ->add('file', FileType::class, ['label' => false, 'required' => false])
            ->end()
            ->with('Introduction', ['class' => 'col-md-6'])
                ->add('textIntro', CKEditorType::class, ['label' => false, 'required' => true])
            ->end()
            ->with('Message d\'aide', ['class' => 'col-md-6'])
                ->add('textHelp', CKEditorType::class, ['label' => false, 'required' => true])
            ->end()
            ->with('Message de confirmation', ['class' => 'col-md-6'])
                ->add('textConfirmation', CKEditorType::class, ['label' => false, 'required' => true])
            ->end()
            ->with('Contenu du mail de billet', ['class' => 'col-md-6'])
                ->add('textTicketEmail', CKEditorType::class, ['label' => false, 'required' => true])
            ->end()
        ;
    }

    /**
     * @param NationalEvent $object
     */
    protected function prePersist(object $object): void
    {
        parent::prePersist($object);

        $this->handleFileUpload($object);
    }

    /**
     * @param NationalEvent $object
     */
    protected function preUpdate(object $object): void
    {
        parent::preUpdate($object);

        $this->handleFileUpload($object);
    }

    #[Required]
    public function setStorage(FilesystemInterface $storage): void
    {
        $this->storage = $storage;
    }

    private function handleFileUpload(NationalEvent $object): void
    {
        if (!$object->file) {
            return;
        }

        $object->intoImagePath = sprintf('/national/events/%s.%s', $object->getUuid()->toString(), $object->file->getClientOriginalExtension());

        $this->storage->put('/static'.$object->intoImagePath, file_get_contents($object->file->getPathname()));
    }
}
