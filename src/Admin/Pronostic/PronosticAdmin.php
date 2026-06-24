<?php

declare(strict_types=1);

namespace App\Admin\Pronostic;

use App\Admin\AbstractAdmin;
use App\Entity\Pronostic\Pronostic;
use App\Form\Admin\UploadableFileType;
use App\Form\DateTimePickerType;
use App\Repository\Pronostic\PronosticRepository;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class PronosticAdmin extends AbstractAdmin
{
    public function __construct(private readonly PronosticRepository $pronosticRepository)
    {
        parent::__construct();
    }

    protected function postPersist(object $object): void
    {
        $this->ensureSingleDisplayed($object);
    }

    protected function postUpdate(object $object): void
    {
        $this->ensureSingleDisplayed($object);
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'matchAt';
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('title', null, ['label' => 'Titre', 'show_filter' => true])
            ->add('team1', null, ['label' => 'Équipe 1'])
            ->add('team2', null, ['label' => 'Équipe 2'])
            ->add('matchAt', null, ['label' => 'Date du match'])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('title', null, ['label' => 'Titre'])
            ->add('team1', null, ['label' => 'Équipe 1'])
            ->add('team2', null, ['label' => 'Équipe 2'])
            ->add('beginAt', null, ['label' => 'Début des pronostics'])
            ->add('matchAt', null, ['label' => 'Date du match'])
            ->add('participantsCount', null, ['label' => 'Participants'])
            ->add('displayed', null, ['label' => 'Affiché', 'editable' => true])
            ->add('resultPublished', null, ['label' => 'Résultat publié'])
            ->add(ListMapper::NAME_ACTIONS, null, ['actions' => ['edit' => []]])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $scoreOptions = [
            'required' => true,
            'attr' => ['min' => 0],
        ];
        $optionalScoreOptions = [
            'required' => false,
            'attr' => ['min' => 0],
        ];

        $form
            ->with('Match', ['class' => 'col-md-6'])
                ->add('title', null, ['label' => 'Titre'])
                ->add('team1', null, ['label' => 'Équipe 1'])
                ->add('team2', null, ['label' => 'Équipe 2'])
                ->add('beginAt', DateTimePickerType::class, ['label' => 'Début des pronostics', 'input' => 'datetime_immutable'])
                ->add('matchAt', DateTimePickerType::class, ['label' => 'Date du match / fin des pronostics', 'input' => 'datetime_immutable'])
                ->add('displayed', CheckboxType::class, [
                    'label' => 'Afficher dans l’application (dans le carrousel d\'alerte)',
                    'required' => false,
                    'help' => 'Un seul pronostic doit être affiché à la fois.',
                ])
                ->add('image', UploadableFileType::class, [
                    'label' => 'Image affichée dans l’alerte',
                    'required' => false,
                ])
            ->end()
            ->with('Pronostic de Gabriel', ['class' => 'col-md-6'])
                ->add('gabrielTeam1Score', IntegerType::class, array_merge($scoreOptions, ['label' => 'Score équipe 1']))
                ->add('gabrielTeam2Score', IntegerType::class, array_merge($scoreOptions, ['label' => 'Score équipe 2']))
            ->end()
            ->with('Résultat', ['class' => 'col-md-6'])
                ->add('resultTeam1Score', IntegerType::class, array_merge($optionalScoreOptions, ['label' => 'Score équipe 1']))
                ->add('resultTeam2Score', IntegerType::class, array_merge($optionalScoreOptions, ['label' => 'Score équipe 2']))
                ->add('publishResult', CheckboxType::class, [
                    'label' => 'Publier le résultat',
                    'required' => false,
                    'help' => 'À cocher lorsque le résultat final est connu. La date de publication est enregistrée automatiquement.',
                ])
            ->end()
        ;
    }

    private function ensureSingleDisplayed(object $object): void
    {
        if ($object instanceof Pronostic && $object->displayed) {
            $this->pronosticRepository->unsetDisplayedExcept($object);
        }
    }
}
