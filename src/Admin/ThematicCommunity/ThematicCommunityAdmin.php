<?php

namespace App\Admin\ThematicCommunity;

use App\Entity\ThematicCommunity\ThematicCommunity;
use App\Image\ImageManager;
use App\Image\ImageManagerInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ThematicCommunityAdmin extends AbstractAdmin
{
    /**
     * @var ImageManager
     */
    private $imageManager;

    /** @required */
    public function setImageManager(ImageManagerInterface $imageManager): void
    {
        $this->imageManager = $imageManager;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('description', null, [
                'label' => 'Description',
            ])
            ->add('_image', 'thumbnail', [
                'label' => 'Bannière',
                'virtual_field' => true,
            ])
            ->add('enabled', null, [
                'label' => 'Active',
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

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Titre',
                'filter_emojis' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'filter_emojis' => true,
            ])
            ->add('image', FileType::class, [
                'label' => 'Bannière',
                'required' => false,
            ])
            ->add('enabled', null, [
                'label' => 'Active',
            ])
        ;
    }

    /**
     * @param ThematicCommunity $thematicCommunity
     */
    public function postRemove($thematicCommunity)
    {
        parent::postRemove($thematicCommunity);

        $this->imageManager->removeImage($thematicCommunity);
    }

    /**
     * @param ThematicCommunity $thematicCommunity
     */
    public function prePersist($thematicCommunity)
    {
        parent::prePersist($thematicCommunity);

        if ($thematicCommunity->getImage()) {
            $this->imageManager->saveImage($thematicCommunity);
        }
    }

    /**
     * @param ThematicCommunity $thematicCommunity
     */
    public function preUpdate($thematicCommunity)
    {
        parent::preUpdate($thematicCommunity);

        if ($thematicCommunity->getImage()) {
            $this->imageManager->saveImage($thematicCommunity);
        }
    }
}
