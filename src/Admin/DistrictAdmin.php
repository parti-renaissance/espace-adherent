<?php

namespace App\Admin;

use App\Entity\Adherent;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DistrictAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter->add('name');
    }

    public static function prepareAutocompleteFilterCallback(self $admin, string $property, string $name): void
    {
        self::autocompleteFilterCallback('managedDistrict', $admin, $property, $name);
    }

    public static function prepareLegislativeCandidateAutocompleteFilterCallback(
        self $admin,
        string $property,
        string $name
    ): void {
        self::autocompleteFilterCallback('legislativeCandidateManagedDistrict', $admin, $property, $name);
    }

    protected static function autocompleteFilterCallback(
        string $field,
        self $admin,
        string $property,
        string $name
    ): void {
        $admin->getDatagrid()->setValue($property, null, $name);
        /** @var QueryBuilder $qb */
        $qb = $admin
            ->getDatagrid()
            ->getQuery()
        ;
        $qb
            ->leftJoin(Adherent::class, 'adherent', Join::WITH, 'adherent.'.$field.' = '.$qb->getRootAliases()[0])
            ->andWhere('adherent IS NULL')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('code', null, [
                'label' => 'Code',
            ])
            ->add('number', null, [
                'label' => 'Numéro',
            ])
            ->add('name', null, [
                'label' => 'Nom',
            ])
            ->add('departmentCode', null, [
                'label' => 'Code du département',
            ])
            ->add('countries', null, [
                'label' => 'Pays',
            ])
            ->add('referentTag', null, [
                'label' => 'Tag référent',
            ])
        ;
    }
}
