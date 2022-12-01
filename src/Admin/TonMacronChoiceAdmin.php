<?php

namespace App\Admin;

use App\Entity\TonMacronChoice;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TonMacronChoiceAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'contentKey';
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
                'choices' => TonMacronChoice::STEPS,
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
                'template' => 'admin/ton_macron/choice_list_actions.html.twig',
            ])
        ;
    }
}
