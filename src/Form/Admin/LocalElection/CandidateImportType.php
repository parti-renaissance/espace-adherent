<?php

declare(strict_types=1);

namespace App\Form\Admin\LocalElection;

use App\LocalElection\CandidateImportCommand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'Ajouter un fichier',
                'help' => 'Le fichier ne doit pas dépasser 5 Mo. Il doit contenir les colonnes CIVILITE;PRENOM;NOM;EMAIL;POSITION;LISTE',
                'attr' => ['accept' => 'text/csv'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', CandidateImportCommand::class);
    }
}
