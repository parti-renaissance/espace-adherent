<?php

namespace App\Admin;

use App\Entity\Administrator;
use App\Entity\QrCode;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Security\Core\Security;

class QrCodeAdmin extends AbstractAdmin
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
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('redirectUrl', null, [
                'label' => 'URL de redirection',
                'show_filter' => true,
            ])
            ->add('count', null, [
                'label' => 'Utilisations',
                'show_filter' => true,
            ])
            ->add('createdBy', null, [
                'label' => 'Auteur',
                'show_filter' => true,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('redirectUrl', null, [
                'label' => 'URL de redirection',
            ])
            ->add('count', null, [
                'label' => 'Utilisations',
            ])
            ->add('createdBy', null, [
                'label' => 'Auteur',
            ])
            ->add('createdAt', null, [
                'label' => 'Créé le',
            ])
            ->add('_qrcode', null, [
                'label' => 'QR Code',
                'virtual_field' => true,
                'template' => 'admin/qr_code/list_qrcode.html.twig',
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
                'label' => 'Nom',
            ])
            ->add('redirectUrl', UrlType::class, [
                'label' => 'URL de redirection',
            ])
        ;
    }

    /**
     * @param QrCode $object
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
