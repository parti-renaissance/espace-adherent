<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RedirectionAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 200,
        '_sort_order' => 'DESC',
        '_sort_by' => 'updatedAt',
    ];

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('from', null, [
                'label' => 'Rediriger depuis ...',
                'help' => 'Uniquement le chemin (sans le domaine) : pour "https://en-marche.fr/marseille", indiquez "/marseille".',
            ])
            ->add('to', null, [
                'label' => 'Vers ...',
                'help' => 'Soit un chemin, soit une URL complète.',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de redirection',
                'choices' => [
                    'Permanente (HTTP 301)' => 301,
                    'Temporaire (HTTP 302)' => 302,
                ],
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('from', null, [
                'label' => 'Rediriger depuis ...',
                'help' => 'Uniquement le chemin (sans le domaine) : pour "https://en-marche.fr/marseille", indiquez "/marseille".',
            ])
            ->add('to', null, [
                'label' => 'Vers ...',
                'help' => 'Soit un chemin, soit une URL complète.',
            ])
            ->add('type', null, [
                'label' => 'Type de redirection',
            ])
            ->add('updatedAt', null, [
                'label' => 'Date de mise à jour',
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
}
