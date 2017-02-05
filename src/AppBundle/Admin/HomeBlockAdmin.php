<?php

namespace AppBundle\Admin;

use AppBundle\Entity\HomeBlock;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class HomeBlockAdmin extends AbstractAdmin
{
    /**
     * @param HomeBlock $object
     *
     * @return Metadata
     */
    public function getObjectMetadata($object)
    {
        return new Metadata($object->getTitle(), $object->getSubtitle(), $object->getMedia()->getPath());
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        if (null === $this->getSubject()->getId()) {
            // Creation
            $formMapper
                ->add('position', null, [
                    'label' => 'Position du bloc',
                ])
                ->add('positionName', null, [
                    'label' => 'Nom du bloc',
                ]);
        }

        $formMapper
            ->add('media', null, [
                'label' => 'Image',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type',
                'choices' => [
                    'Article' => 'article',
                    'Vidéo' => 'video',
                ],
            ])
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('subtitle', null, [
                'label' => 'Sous-titre',
            ])
            ->add('link', null, [
                'label' => 'Cible du lien',
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('positionName', null, [
                'label' => 'Bloc',
            ])
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('link', null, [
                'label' => 'Cible du lien',
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
