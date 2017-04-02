<?php

namespace AppBundle\Admin;

use AppBundle\Entity\SocialShare;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\CoreBundle\Model\Metadata;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class SocialShareAdmin extends AbstractAdmin
{
    use MediaSynchronisedAdminTrait;

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
}
