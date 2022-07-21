<?php

namespace App\Admin;

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommitmentAdmin extends AbstractAdmin
{
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier('title', null, ['label' => 'Titre'])
            ->add('position', null, ['label' => 'Position'])
            ->add('createdAt', null, ['label' => 'Créée le'])
            ->add('updatedAt', null, ['label' => 'Modifiée le'])
        ;
    }

    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Général', ['class' => 'col-md-6'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('position', IntegerType::class, [
                    'attr' => ['min' => 0],
                    'label' => 'Position',
                    'help' => 'Plus la position est élevée plus le block descendra sur la page.',
                ])
                ->add('shortDescription', null, ['label' => 'Description courte'])
                ->add('description', TextareaType::class, [
                    'label' => 'Description complète',
                    'attr' => ['class' => 'simplified-content-editor', 'rows' => 20],
                    'help' => 'help.markdown',
                    'help_html' => true,
                    'required' => false,
                ])
            ->end()
            ->with('Photo', ['class' => 'col-md-6'])
                ->add('image', FileType::class, [
                    'label' => 'Ajoutez une photo',
                    'help' => 'La photo ne doit pas dépasser 5 Mo.',
                    'required' => $this->isCreation(),
                ])
            ->end()
        ;
    }
}
