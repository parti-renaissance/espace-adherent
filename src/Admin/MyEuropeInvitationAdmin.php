<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;

class MyEuropeInvitationAdmin extends AbstractAdmin
{
    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
    }

    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('create');
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('authorEmailAddress', null, [
                'label' => 'Email de l’auteur',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('id', null, [
                'label' => 'ID',
            ])
            ->add('friendAge', null, [
                'label' => 'Age de l’ami',
            ])
            ->add('friendGender', null, [
                'label' => 'Genre de l’ami',
            ])
            ->add('authorFirstName', null, [
                'label' => 'Prénom de l’auteur',
            ])
            ->add('authorEmailAddress', null, [
                'label' => 'Email de l’auteur',
            ])
            ->add('mailSubject', null, [
                'label' => 'Sujet',
            ])
            ->add('mailBody', null, [
                'label' => 'Contenu',
                'template' => 'admin/interactive/invitation_mail_body.html.twig',
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
        ;
    }
}
