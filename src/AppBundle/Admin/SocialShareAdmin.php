<?php

namespace AppBundle\Admin;

use AppBundle\Entity\SocialShare;
use League\Flysystem\Filesystem;
use League\Glide\Server;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SocialShareAdmin extends AbstractAdmin
{
    /**
     * @var Filesystem
     */
    private $storage;

    /**
     * @var Server
     */
    private $glide;

    /**
     * @param SocialShare $socialShare
     */
    public function prePersist($socialShare)
    {
        parent::prePersist($socialShare);

        $this->storage->put(
            'images/'.$socialShare->getMedia()->getPath(),
            file_get_contents($socialShare->getMedia()->getFile()->getPathname())
        );

        $this->glide->deleteCache('images/'.$socialShare->getMedia()->getPath());
    }

    /**
     * @param SocialShare $socialShare
     */
    public function preUpdate($socialShare)
    {
        parent::preUpdate($socialShare);

        if ($socialShare->getMedia()->getFile()) {
            $this->storage->put(
                'images/'.$socialShare->getMedia()->getPath(),
                file_get_contents($socialShare->getMedia()->getFile()->getPathname())
            );

            $this->glide->deleteCache('images/'.$socialShare->getMedia()->getPath());
        }
    }

    /**
     * @param SocialShare $object
     *
     * @return Metadata
     */
    public function getObjectMetadata($object)
    {
        return new Metadata($object->getName(), null, $object->getMedia()->getPath());
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Média', ['class' => 'col-md-6'])
                ->add('media', AdminType::class, [
                    'label' => 'Média',
                ])
            ->end()
            ->with('Données sociales', ['class' => 'col-md-6'])
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('type', ChoiceType::class, [
                    'label' => 'Type',
                    'choices' => array_combine(SocialShare::TYPES, SocialShare::TYPES),
                ])
                ->add('socialShareCategory', null, [
                    'label' => 'Catégorie',
                ])
                ->add('defaultUrl', null, [
                    'label' => 'URL par défaut ',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                ])
                ->add('twitterUrl', null, [
                    'label' => 'Url Twitter',
                ])
                ->add('facebookUrl', null, [
                    'label' => 'Url Facebook',
                ])
                ->add('position', null, [
                    'label' => 'Position',
                ])
                ->add('published', null, [
                    'label' => 'Publié ?',
                ])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->addIdentifier('type', null, [
                'label' => 'Type',
            ])
            ->add('socialShareCategory', null, [
                'label' => 'Catégorie',
            ])
            ->add('media', null, [
                'label' => 'Média',
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('published', null, [
                'label' => 'Publié ?',
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
            ])
        ;
    }

    public function setStorage(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    public function setGlide(Server $glide)
    {
        $this->glide = $glide;
    }
}
