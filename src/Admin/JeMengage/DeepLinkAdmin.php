<?php

namespace App\Admin\JeMengage;

use App\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class DeepLinkAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('Général', ['class' => 'col-md-6', 'description' => 'Note: modification du lien est impossible après sa création'])
                ->add('label', TextType::class, ['label' => 'Libellé'])
                ->add('link', UrlType::class, [
                    'default_protocol' => 'https',
                    'label' => 'Lien 🔗',
                    'help' => 'Domaines autorisés : <strong>avecvous.fr</strong>, <strong>en-marche.fr</strong> et <strong>je-mengage.fr</strong>',
                    'attr' => [
                        'placeholder' => 'https://app.avecvous.fr/liste-evenements',
                    ],
                ])
            ->end()
            ->with('Metadata', ['class' => 'col-md-6'])
                ->add('socialTitle', TextType::class, ['label' => 'Titre', 'required' => false])
                ->add('socialDescription', TextareaType::class, ['label' => 'Description', 'required' => false])
            ->end()
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, ['label' => '#'])
            ->add('label', null, ['label' => 'Lien'])
            ->add('link', null, ['label' => 'Lien'])
            ->add('socialTitle', null, ['label' => 'Metadata: titre'])
            ->add('socialDescription', null, ['label' => 'Metadata: description'])
            ->add('createdAt', null, ['label' => 'Créé le'])
        ;
    }
}
