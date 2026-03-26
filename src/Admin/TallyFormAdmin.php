<?php

declare(strict_types=1);

namespace App\Admin;

use App\Form\Type\DatalistTextType;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class TallyFormAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('published', null, [
                'label' => 'Publié',
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('tallyId', null, [
                'label' => 'Tally ID',
            ])
            ->add('published', null, [
                'label' => 'Publié',
            ])
            ->add('utmSource', null, [
                'label' => 'UTM Source',
            ])
            ->add('utmCampaign', null, [
                'label' => 'UTM Campaign',
            ])
            ->add('updatedAt', null, [
                'label' => 'Dernière mise à jour',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'link' => [
                        'template' => 'admin/tally_form/action_link.html.twig',
                    ],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Informations', ['class' => 'col-md-6'])
                ->add('title', TextType::class, [
                    'label' => 'Titre',
                ])
                ->add('slug', TextType::class, [
                    'label' => 'Slug',
                    'help' => 'Identifiant unique dans l\'URL (lettres minuscules, chiffres, tirets et slashs)',
                ])
                ->add('tallyId', TextType::class, [
                    'label' => 'Tally ID',
                    'help' => 'Identifiant du formulaire Tally (ex: wgDzz1)',
                ])
            ->end()
            ->with('Tracking UTM', ['class' => 'col-md-6'])
                ->add('utmSource', DatalistTextType::class, [
                    'label' => 'UTM Source',
                    'required' => false,
                    'datalist_options' => ['consultation', 'convention'],
                ])
                ->add('utmCampaign', TextType::class, [
                    'label' => 'UTM Campaign',
                    'required' => false,
                ])
            ->end()
            ->with('Publication', ['class' => 'col-md-6'])
                ->add('published', CheckboxType::class, [
                    'label' => 'Publié',
                    'required' => false,
                    'help' => 'Cochez cette case pour rendre le formulaire accessible',
                ])
            ->end()
        ;
    }
}
