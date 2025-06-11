<?php

namespace App\Admin;

use App\Entity\Administrator;
use App\Entity\QrCode;
use App\QrCode\QrCodeHostEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class QrCodeAdmin extends AbstractAdmin
{
    public function __construct(private readonly Security $security)
    {
        parent::__construct();
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 128;
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('host', ChoiceFilter::class, [
                'field_type' => ChoiceType::class,
                'field_options' => [
                    'choices' => QrCodeHostEnum::ALL,
                    'choice_label' => function (string $choice) {
                        return $choice;
                    },
                ],
                'label' => 'Domaine',
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

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('host', null, [
                'label' => 'Domaine',
                'template' => 'admin/qr_code/list_host.html.twig',
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
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('name', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('redirectUrl', UrlType::class, [
                'label' => 'URL de redirection',
                'help' => 'L\'url sur laquelle redirigera le QR Code',
            ])
            ->add('host', ChoiceType::class, [
                'label' => 'Domaine',
                'help' => 'Domaine utilisé pour générer l\'url du QR Code',
                'choices' => QrCodeHostEnum::ALL,
                'choice_label' => function (string $choice) {
                    return $choice;
                },
            ])
        ;
    }

    /**
     * @param QrCode $object
     */
    protected function prePersist(object $object): void
    {
        /** @var Administrator $administrator */
        $administrator = $this->security->getUser();

        $object->setCreatedBy($administrator);
    }
}
