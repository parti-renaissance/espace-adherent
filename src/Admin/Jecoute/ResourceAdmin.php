<?php

namespace App\Admin\Jecoute;

use App\Admin\AbstractAdmin;
use App\Entity\Jecoute\Resource;
use App\Image\ImageManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ResourceAdmin extends AbstractAdmin
{
    private ImageManagerInterface $imageManager;

    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = parent::createQuery($context);
        $proxyQuery->addOrderBy('o.position', 'ASC');

        return $proxyQuery;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
        $collection->add('move', $this->getRouterIdParameter().'/move/{position}');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
             ->add('label', null, [
                 'label' => 'Label',
                 'show_filter' => true,
             ])
             ->add('url', null, [
                 'label' => 'Url',
                 'show_filter' => true,
             ])
         ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('label', null, [
                'label' => 'Label',
            ])
            ->add('url', null, [
                'label' => 'Url',
            ])
            ->add('_image', 'thumbnail', [
                'label' => 'Photo',
                'virtual_field' => true,
            ])
            ->add('position', null, [
                'label' => 'Position',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'move' => [
                        'template' => '@PixSortableBehavior/Default/_sort_drag_drop.html.twig',
                        'enable_top_bottom_buttons' => true,
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Général', ['class' => 'col-md-6'])
                ->add('label', TextType::class, [
                    'label' => 'Label',
                ])
                ->add('url', UrlType::class, [
                    'label' => 'Url',
                ])
                ->add('position', IntegerType::class, [
                    'label' => 'Ordre d\'affichage',
                    'required' => false,
                    'scale' => 0,
                    'attr' => [
                        'min' => 0,
                    ],
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-6'])
                ->add('image', FileType::class, [
                    'label' => 'Ajoutez une photo',
                    'help' => 'La photo ne doit pas dépasser 5 Mo.',
                ])
            ->end()
        ;
    }

    /**
     * @param Resource $resource
     */
    public function prePersist($resource)
    {
        parent::prePersist($resource);

        if ($resource->getImage()) {
            $this->imageManager->saveImage($resource);
        }
    }

    /**
     * @param Resource $resource
     */
    public function preUpdate($resource)
    {
        parent::preUpdate($resource);

        if ($resource->getImage()) {
            $this->imageManager->saveImage($resource);
        }
    }

    /**
     * @param Resource $resource
     */
    public function postRemove($resource)
    {
        parent::postRemove($resource);

        $this->imageManager->removeImage($resource);
    }

    /** @required */
    public function setImageManager(ImageManagerInterface $imageManager): void
    {
        $this->imageManager = $imageManager;
    }
}
