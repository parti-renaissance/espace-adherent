<?php

namespace App\Admin;

use App\Entity\MyEuropeChoice;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class MyEuropeChoiceAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'contentKey';
        $sortValues[DatagridInterface::PER_PAGE] = 200;
    }

    protected function configureBatchActions(array $actions): array
    {
        $actions = parent::configureBatchActions($actions);
        unset($actions['delete']);

        return $actions;
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $formMapper
            ->add('contentKey', null, [
                'label' => 'Clé',
            ])
            ->add('label', null, [
                'label' => 'Label',
            ])
            ->add('content', null, [
                'label' => 'Message',
                'attr' => ['rows' => 10],
            ])
            ->add('step', ChoiceType::class, [
                'label' => 'Étape',
                'choices' => MyEuropeChoice::STEPS,
                'choice_translation_domain' => 'forms',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('contentKey', null, [
                'label' => 'Clé',
            ])
            ->add('label', null, [
                'label' => 'Label',
            ])
            ->add('content', null, [
                'label' => 'Message',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'template' => 'admin/interactive/choice_list_actions.html.twig',
            ])
        ;
    }
}
