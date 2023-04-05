<?php

namespace App\Form\Admin;

use App\Entity\Adherent;
use App\Renaissance\Membership\RenaissanceMembershipFilterEnum;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RenaissanceAdherentAutocompleteType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'property' => ['firstName', 'lastName', 'emailAddress'],
            'class' => Adherent::class,
            'template' => 'admin/form/sonata_type_model_autocomplete.html.twig',
            'callback' => [self::class, 'filterCallback'],
        ]);
    }

    public static function filterCallback(AbstractAdmin $admin, array $property, $value): void
    {
        $datagrid = $admin->getDatagrid();
        $previousFilter = $filter = $datagrid->getFilter('renaissanceMembership');
        $datagrid->setValue($filter->getName(), null, RenaissanceMembershipFilterEnum::ADHERENT_RE);

        foreach ($property as $prop) {
            $filter = $datagrid->getFilter($prop);
            $filter->setCondition(FilterInterface::CONDITION_OR);

            if (null !== $previousFilter) {
                $filter->setPreviousFilter($previousFilter);
            }

            $datagrid->setValue($filter->getFormName(), null, $value);

            $previousFilter = $filter;
        }

        $datagrid->reorderFilters($property);
    }

    public function getParent(): ?string
    {
        return ModelAutocompleteType::class;
    }
}
