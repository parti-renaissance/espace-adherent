<?php

namespace App\Admin;

use App\Entity\Mooc\BaseMoocElement;
use App\Entity\Mooc\Mooc;
use Doctrine\ORM\QueryBuilder;
use Runroom\SortableBehaviorBundle\Admin\SortableAdminTrait;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\Form\Type\DatePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MoocChapterAdmin extends AbstractAdmin
{
    use SortableAdminTrait;

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
                        'template' => '@RunroomSortableBehavior/sort.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
