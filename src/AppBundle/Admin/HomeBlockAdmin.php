<?php

namespace AppBundle\Admin;

use AppBundle\Entity\HomeBlock;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class HomeBlockAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 32,
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];

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
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'filter_emojis' => true,
            ])
            ->add('subtitle', TextType::class, [
                'label' => 'Sous-titre',
                'filter_emojis' => true,
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
