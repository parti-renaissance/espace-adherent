<?php

namespace AppBundle\Admin;

use AppBundle\Entity\Media;
use League\Flysystem\Filesystem;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
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
     * @param Media $media
     */
    public function preValidate($media)
    {
        try { $this->storage->delete('images/'.$media->getPath()); } catch(\Exception $e) {}
    }

    /**
     * @param Media $media
     */
    public function preRemove($media)
    {
        try { $this->storage->delete('images/'.$media->getPath()); } catch(\Exception $e) {}
    }

    /**
     * @param Media $media
     */
    public function prePersist($media)
    {
        $this->storage->write('images/'.$media->getPath(), file_get_contents($media->getFile()->getPathname()));
    }

    /**
     * @param Media $media
     */
    public function preUpdate($media)
    {
        $this->storage->write('images/'.$media->getPath(), file_get_contents($media->getFile()->getPathname()));
    }

    /**
     * @param Media $object
     * @return Metadata
     */
    public function getObjectMetadata($object)
    {
        return new Metadata($object->getName(), null, $object->getPath());
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('path', TextType::class, [
                'label' => 'URL (ne spécifier que la fin : http://en-marche.fr/assets/images/<votre-valeur>)',
            ])
            ->add('file', FileType::class, [
                'label' => 'Image'
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('path', null, [
                'label' => 'Nom',
            ])
            ->add('mimeType', null, [
                'label' => 'Type de fichier',
            ]);
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
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
                'label' => 'Largeur (en pixels)',
            ])
            ->add('height', null, [
                'label' => 'Hauteur (en pixels)',
            ])
            ->add('_action', null, [
                'actions' => [
                    'preview' => [
                        'template' => 'admin/media_preview.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    public function setStorage(Filesystem $storage)
    {
        $this->storage = $storage;
    }
}
