<?php

declare(strict_types=1);

namespace App\Admin;

use App\Form\PositionType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class HubItemAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('', ['class' => 'col-md-6'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('url', UrlType::class, ['label' => 'Lien', 'default_protocol' => 'https'])
                ->add('position', PositionType::class, ['label' => 'Position', 'required' => false])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, [
                'label' => 'Titre',
            ])
            ->add('url', null, ['label' => 'URL'])
            ->add('position', null, ['label' => 'Position'])
            ->add('createdAt', null, ['label' => 'Créé le'])
            ->add('updatedAt', null, ['label' => 'Modifié le'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
