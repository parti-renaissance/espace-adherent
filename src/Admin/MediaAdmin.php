<?php

namespace App\Admin;

use App\Entity\Media;
use League\Flysystem\FilesystemInterface;
use League\Glide\Server;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MediaAdmin extends AbstractAdmin
{
    /**
     * @var FilesystemInterface
     */
    private $storage;

    /**
     * @var Server
     */
    private $glide;

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    /**
     * @param Media $media
     */
    protected function preRemove(object $media): void
    {
        parent::preRemove($media);

        try {
            $this->storage->delete($media->getPathWithDirectory());
            $this->glide->deleteCache($media->getPathWithDirectory());
        } catch (\Exception $e) {
        }
    }

    /**
     * @param Media $media
     */
    protected function prePersist(object $media): void
    {
        parent::prePersist($media);

        $this->storage->put($media->getPathWithDirectory(), file_get_contents($media->getFile()->getPathname()));
        $this->glide->deleteCache($media->getPathWithDirectory());
    }

    /**
     * @param Media $media
     */
    protected function preUpdate(object $media): void
    {
        parent::preUpdate($media);

        if ($media->getFile()) {
            $this->storage->put($media->getPathWithDirectory(), file_get_contents($media->getFile()->getPathname()));
            $this->glide->deleteCache($media->getPathWithDirectory());
        }
    }

    /**
     * @param Media $object
     */
    public function getObjectMetadata(object $object): MetadataInterface
    {
        return new Metadata($object->getName(), null, $object->getPath());
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $isCreation = null === $this->getSubject() || null === $this->getSubject()->getSize();

        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('path', null, [
                'label' => $isCreation ? 'URL (ne spécifier que la fin : http://en-marche.fr/assets/images/<votre-valeur>, doit être unique)' : 'URL (non modifiable)',
                'disabled' => !$isCreation,
            ])
            ->add('file', FileType::class, [
                'label' => $isCreation ? 'Image / Vidéo' : 'Image / Vidéo (laisser vide pour ne pas modifier)',
                'required' => $isCreation,
            ])
            ->add('compressedDisplay', null, [
                'label' => 'L\'affichage compressé (compression appliquée seulement aux images)',
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('path', null, [
                'label' => 'Chemin',
            ])
            ->add('mimeType', null, [
                'label' => 'Type de fichier',
            ])
            ->add('compressedDisplay', null, [
                'label' => 'L\'affichage compressé',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('_thumbnail', null, [
                'label' => 'Miniature',
                'virtual_field' => true,
                'template' => 'admin/list/list_thumbnail.html.twig',
            ])
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('path', null, [
                'label' => 'URL',
            ])
            ->add('mimeType', null, [
                'label' => 'Type de fichier',
            ])
            ->add('width', null, [
                'label' => 'Largeur',
            ])
            ->add('height', null, [
                'label' => 'Hauteur',
            ])
            ->add('compressedDisplay', null, [
                'label' => 'L\'affichage compressé',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de dernière mise à jour',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/media/list_actions.html.twig',
            ])
        ;
    }

    public function setStorage(FilesystemInterface $storage): void
    {
        $this->storage = $storage;
    }

    public function setGlide(Server $glide): void
    {
        $this->glide = $glide;
    }
}
