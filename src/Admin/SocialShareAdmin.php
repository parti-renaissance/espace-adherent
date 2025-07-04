<?php

namespace App\Admin;

use App\Entity\SocialShare;
use App\Twig\AssetRuntime;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\AdminType;
use Sonata\AdminBundle\Object\Metadata;
use Sonata\AdminBundle\Object\MetadataInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SocialShareAdmin extends AbstractAdmin
{
    use MediaSynchronisedAdminTrait;

    public function __construct(private readonly AssetRuntime $assetRuntime)
    {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    /**
     * @param SocialShare $object
     */
    public function getObjectMetadata(object $object): MetadataInterface
    {
        return new Metadata($object->getName(), null, $object->getMedia()->getPath());
    }

    /**
     * @param SocialShare $object
     */
    protected function prePersist(object $object): void
    {
        // Upload
        $this->storage->write(
            'images/'.$object->getMedia()->getPath(),
            file_get_contents($object->getMedia()->getFile()->getPathname())
        );

        $this->glide->deleteCache('images/'.$object->getMedia()->getPath());

        // Default URL
        if (!$object->getDefaultUrl()) {
            // Trick to generate the URL before persisting
            $object->getMedia()->setUpdatedAt(new \DateTime());

            $object->setDefaultUrl(
                $this->assetRuntime->transformedMediaAsset($object->getMedia(), [], UrlGeneratorInterface::ABSOLUTE_URL)
            );
        }

        // Name
        if (!$object->getName()) {
            $object->setName($object->getMedia()->getName());
        }
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Média', ['class' => 'col-md-6'])
                ->add('media', AdminType::class, [
                    'label' => 'Média',
                ])
            ->end()
            ->with('Données sociales', ['class' => 'col-md-6'])
                ->add('published', null, [
                    'label' => 'Publié ?',
                ])
                ->add('position', null, [
                    'label' => 'Position',
                ])
                ->add('type', ChoiceType::class, [
                    'label' => 'Type',
                    'choices' => array_combine(SocialShare::TYPES, SocialShare::TYPES),
                ])
                ->add('socialShareCategory', null, [
                    'label' => 'Catégorie',
                ])
                ->add('description', null, [
                    'label' => 'Description',
                ])
                ->add('name', TextType::class, [
                    'label' => 'Nom',
                    'required' => false,
                    'help' => 'Laissez vide pour réutiliser le nom du média',
                ])
                ->add('defaultUrl', null, [
                    'label' => 'URL par défaut',
                    'required' => false,
                    'help' => 'Laissez vide pour réutiliser l\'URL du média',
                ])
                ->add('twitterUrl', null, [
                    'label' => 'URL pour Twitter',
                    'required' => false,
                    'help' => 'Laissez vide pour utiliser l\'URL par défaut',
                ])
                ->add('facebookUrl', null, [
                    'label' => 'URL pour Facebook',
                    'required' => false,
                    'help' => 'Laissez vide pour utiliser l\'URL par défaut',
                ])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
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
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
