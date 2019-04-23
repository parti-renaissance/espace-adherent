<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Media;
use League\Flysystem\Filesystem;
use League\Glide\Server;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MediaAdmin extends AbstractAdmin
{
    /**
     * @var Filesystem
     */
    private $storage;

    /**
     * @var Server
     */
    private $glide;

    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    /**
     * @param Media $media
     */
    public function preRemove($media)
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
    public function prePersist($media)
    {
        parent::prePersist($media);

        $this->storage->put($media->getPathWithDirectory(), file_get_contents($media->getFile()->getPathname()));
        $this->glide->deleteCache($media->getPathWithDirectory());
    }

    /**
     * @param Media $media
     */
    public function preUpdate($media)
    {
        parent::preUpdate($media);

        if ($media->getFile()) {
            $this->storage->put($media->getPathWithDirectory(), file_get_contents($media->getFile()->getPathname()));
            $this->glide->deleteCache($media->getPathWithDirectory());
        }
    }

    /**
     * @param Media $object
     *
     * @return Metadata
     */
    public function getObjectMetadata($object)
    {
        return new Metadata($object->getName(), null, $object->getPath());
    }

    public function getTemplate($name)
    {
        if ('outer_list_rows_mosaic' === $name) {
            return 'admin/media/mosaic.html.twig';
        }

        return parent::getTemplate($name);
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $isCreation = null === $this->getSubject() || null === $this->getSubject()->getSize();

        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
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

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
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

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('_thumbnail', null, [
                'label' => 'Miniature',
                'virtual_field' => true,
                'template' => 'admin/media/list_thumbnail.html.twig',
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
            ->add('_action', null, [
                'virtual_field' => true,
                'template' => 'admin/media/list_actions.html.twig',
            ])
        ;
    }

    public function setStorage(Filesystem $storage): void
    {
        $this->storage = $storage;
    }

    public function setGlide(Server $glide): void
    {
        $this->glide = $glide;
    }
}
