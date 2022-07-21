<?php

namespace App\Admin\JeMengage;

use App\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class HeaderBlockAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        if (!$this->isCreation()) {
            $formMapper
                ->with('Général', ['class' => 'col-md-6'])
                    ->add('slug', null, [
                        'label' => 'Slug',
                        'disabled' => true,
                    ])
                ->end()
            ;
        }

        $formMapper
            ->with('Général', ['class' => 'col-md-6'])
                ->add('name', null, [
                    'label' => 'Nom',
                ])
                ->add('prefix', null, [
                    'label' => 'Préfixe du slogan',
                ])
                ->add('slogan', null, [
                    'label' => 'Slogan',
                    'required' => false,
                    'help' => 'Ne spécifiez que la fin du slogan. Ex: [prefix] [votre valeur]',
                ])
                ->add('content', TextareaType::class, [
                    'label' => 'Contenu',
                    'required' => false,
                    'attr' => ['class' => 'simplified-content-editor', 'rows' => 20],
                    'help' => <<<HELP
Veuillez restreindre le contenu au format <a href="https://www.markdownguide.org/basic-syntax/" target="_blank">Markdown.</a><br/>
Si une date d'échéance est spécifiée, rajoutez la balise <strong>{{ date_echeance }}</strong> dans le texte.<br/>
Pour indiquer le prénom d'un utilisateur dans le message de bienvenue par exemple, rajouter la balise <strong>{{ prenom }}</strong> dans le texte.
HELP
,
                ])
                ->add('deadlineDate', null, [
                    'label' => 'Date d\'échéance',
                    'required' => false,
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-6'])
                ->add('image', FileType::class, [
                    'label' => 'Ajoutez une photo',
                    'help' => 'La photo ne doit pas dépasser 5 Mo.',
                    'required' => false,
                ])
            ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('slug', null, [
                'label' => 'Slug',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('_image', 'thumbnail', [
                'label' => 'Image',
                'virtual_field' => true,
            ])
            ->add('updatedAt', null, [
                 'label' => 'Dernière mise à jour',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }
}
