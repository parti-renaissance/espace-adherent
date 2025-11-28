<?php

declare(strict_types=1);

namespace App\Admin;

use App\Form\Admin\SimpleMDEContent;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;

class LiveStreamAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Événement', ['class' => 'col-md-7'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('description', SimpleMDEContent::class, [
                    'label' => 'Description',
                    'required' => false,
                    'attr' => ['rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                ])
                ->add('beginAt', null, ['label' => 'Date de début', 'widget' => 'single_text'])
                ->add('finishAt', null, ['label' => 'Date de fin', 'widget' => 'single_text'])
                ->add('url', null, ['label' => 'URL', 'help' => 'https://player.vimeo.com/video/123456789'])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, [
                'label' => 'Id',
            ])
            ->add('title', null, ['label' => 'Titre'])
            ->add('beginAt', null, ['label' => 'Date de début'])
            ->add('finishAt', null, ['label' => 'Date de fin'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                    'link' => [
                        'template' => 'admin/live_stream/action_link.html.twig',
                    ],
                ],
            ])
        ;
    }
}
