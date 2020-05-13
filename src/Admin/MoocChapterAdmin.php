<?php

namespace App\Admin;

use App\Entity\Mooc\BaseMoocElement;
use App\Entity\Mooc\Mooc;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\CoreBundle\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MoocChapterAdmin extends AbstractAdmin
{
    public function createQuery($context = 'list')
    {
        /** @var QueryBuilder $proxyQuery */
        $proxyQuery = parent::createQuery($context);
        $proxyQuery->addOrderBy('o.mooc', 'ASC');
        $proxyQuery->addOrderBy('o.position', 'ASC');

        return $proxyQuery;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('Chapitres')
                ->with('Général', ['class' => 'col-md-6'])
                    ->add('title', TextType::class, [
                        'label' => 'Titre',
                        'filter_emojis' => true,
                    ])
                    ->add('slug', TextType::class, [
                        'label' => 'Slug',
                        'disabled' => true,
                    ])
                    ->add('published', CheckboxType::class, [
                        'label' => 'Publié',
                        'required' => false,
                    ])
                    ->add('publishedAt', DatePickerType::class, [
                        'label' => 'Date de publication',
                    ])
                    ->add('mooc', EntityType::class, [
                        'class' => Mooc::class,
                        'placeholder' => 'Sélectionner un Mooc',
                    ])
                    ->add('position', IntegerType::class, [
                        'label' => 'Ordre d\'affichage',
                        'scale' => 0,
                        'attr' => [
                            'min' => 0,
                        ],
                    ])
                ->end()
        ;
        if (!$this->request->isXmlHttpRequest()) {
            $formMapper
                ->with('Media', ['class' => 'col-md-6'])
                    ->add('elements', EntityType::class, [
                        'label' => 'Éléments',
                        'multiple' => true,
                        'class' => BaseMoocElement::class,
                        'by_reference' => false,
                    ])
                ->end()
            ;
        }
        $formMapper
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('published', null, [
                'label' => 'Publié',
                'editable' => true,
            ])
            ->add('publishedAt', null, [
                'label' => 'Date de publication',
            ])
            ->add('mooc', null, [
                'label' => 'MOOC associé',
            ])
            ->add('position', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->add('_action', null, [
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

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
        $collection->add('move', $this->getRouterIdParameter().'/move/{position}');
    }
}
