<?php

namespace App\Admin\Jecoute;

use App\Entity\Administrator;
use App\Entity\Jecoute\Riposte;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Security\Core\Security;

class RiposteAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    private $security;

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('body', null, [
                'label' => 'Texte',
            ])
            ->add('sourceUrl', null, [
                'label' => 'Url',
            ])
            ->add('withNotification', null, [
                'label' => 'Avec notification',
            ])
            ->add('enabled', null, [
                'label' => 'Active',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('body', null, [
                'label' => 'Texte',
            ])
            ->add('sourceUrl', null, [
                'label' => 'URL',
            ])
            ->add('withNotification', null, [
                'editable' => true,
                'label' => 'Avec notification',
            ])
            ->add('enabled', null, [
                'editable' => true,
                'label' => 'Active',
            ])
            ->add('creator', null, [
                'virtual_field' => true,
                'label' => 'Auteur',
                'template' => 'admin/jecoute/riposte/list_creator.html.twig',
            ])
            ->add('nbViews', null, [
                'label' => 'Nb de vues',
                'header_style' => 'max-width: 70px',
            ])
            ->add('ndDetailViews', null, [
                'label' => 'Nb de vues détaillées',
                'header_style' => 'max-width: 70px',
            ])
            ->add('nbSourceViews', null, [
                'label' => 'Nb de vues de la source',
                'header_style' => 'max-width: 70px',
            ])
            ->add('nbRipostes', null, [
                'label' => 'Nb de ripostes',
                'header_style' => 'max-width: 70px',
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
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'filter_emojis' => true,
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Texte',
                'filter_emojis' => true,
                'attr' => ['rows' => 10],
            ])
            ->add('sourceUrl', UrlType::class, [
                'label' => 'URL',
                'required' => false,
            ])
            ->add('withNotification', null, [
                'label' => 'Avec notification',
            ])
            ->add('enabled', null, [
                'label' => 'Active',
            ])
        ;
    }

    /**
     * @param Riposte $object
     */
    public function prePersist($object)
    {
        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setCreatedBy($administrator);
    }

    /**
     * @required
     */
    public function setSecurity(Security $security)
    {
        $this->security = $security;
    }
}
