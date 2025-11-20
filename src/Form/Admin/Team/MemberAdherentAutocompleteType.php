<?php

namespace App\Form\Admin\Team;

use App\Entity\Adherent;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Filter\FilterInterface;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberAdherentAutocompleteType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['admin_code'] = 'app.admin.team.member';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'quiet_millis' => 500,
            'placeholder' => '...',
            'label' => false,
            'minimum_input_length' => 1,
            'items_per_page' => 20,
            'property' => [
                'id',
                'firstName',
                'lastName',
                'emailAddress',
            ],
            'class' => Adherent::class,
            'to_string_callback' => [self::class, 'toStringCallback'],
            'req_params' => [
                'field' => 'adherent',
                '_context' => 'form',
            ],
            'callback' => [self::class, 'filterCallback'],
        ]);
    }

    public static function toStringCallback(Adherent $adherent): string
    {
        return \sprintf(
            '%s %s (%s)',
            $adherent->getFirstName(),
            $adherent->getLastName(),
            $adherent->getEmailAddress()
        );
    }

    public static function filterCallback(AbstractAdmin $admin, array $property, $value): void
    {
        $datagrid = $admin->getDatagrid();
        $previousFilter = $filter = $datagrid->getFilter('adherent');
        $filter->setCondition(FilterInterface::CONDITION_AND);
        $datagrid->setValue($filter->getName(), null, true);

        foreach ($property as $prop) {
            $filter = $datagrid->getFilter($prop);
            $filter->setCondition(FilterInterface::CONDITION_OR);
            if ($previousFilter) {
                $filter->setPreviousFilter($previousFilter);
            }
            $datagrid->setValue($filter->getFormName(), null, $value);
            $previousFilter = $filter;
        }

        $datagrid->reorderFilters($property);
    }

    public function getParent(): string
    {
        return ModelAutocompleteType::class;
    }
}
