<?php

declare(strict_types=1);

namespace App\Admin\Poll;

use App\Entity\Poll\PollResultDisplayModeEnum;
use App\Form\Admin\Poll\PollChoiceType;
use App\Form\DateTimePickerType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateRangeFilter;
use Sonata\Form\Type\DateRangePickerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class PollAdmin extends AbstractAdmin
{
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('delete');
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        parent::configureDefaultSortValues($sortValues);

        $sortValues[DatagridInterface::SORT_BY] = 'createdAt';
        $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
        $sortValues[DatagridInterface::PER_PAGE] = 128;
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('question', null, [
                'label' => 'Question',
                'show_filter' => true,
            ])
            ->add('finishAt', DateRangeFilter::class, [
                'label' => 'Date de fin',
                'field_type' => DateRangePickerType::class,
            ])
            ->add('createdAt', DateRangeFilter::class, [
                'label' => 'Date de création',
                'show_filter' => true,
                'field_type' => DateRangePickerType::class,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('question', null, [
                'label' => 'Question',
            ])
            ->add('startAt', null, [
                'label' => 'Date de début',
            ])
            ->add('finishAt', null, [
                'label' => 'Date de fin',
            ])
            ->add('resultDisplayEndAt', null, [
                'label' => 'Fin d’affichage des résultats',
            ])
            ->add('published', null, [
                'label' => 'Activé',
                'editable' => true,
            ])
            ->add('createdAt', null, [
                'label' => 'Date de création',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Questionnaire', ['class' => 'col-md-6'])
                ->add('question', TextType::class, [
                    'label' => 'Question',
                ])
                ->add('description', TextareaType::class, [
                    'label' => 'Description',
                    'required' => false,
                ])
                ->add('choices', CollectionType::class, [
                    'entry_type' => PollChoiceType::class,
                    'required' => true,
                    'label' => 'Choix',
                    'by_reference' => false,
                    'allow_add' => true,
                ])
            ->end()
            ->with('Configuration', ['class' => 'col-md-6'])
                ->add('startAt', DateTimePickerType::class, [
                    'label' => 'Date de début',
                    'input' => 'datetime_immutable',
                ])
                ->add('finishAt', DateTimePickerType::class, [
                    'label' => 'Date de fin',
                    'input' => 'datetime_immutable',
                ])
                ->add('resultDisplayEndAt', DateTimePickerType::class, [
                    'label' => 'Fin d’affichage des résultats',
                    'required' => false,
                    'input' => 'datetime_immutable',
                    'help' => 'Si vide, les résultats ne restent visibles que jusqu’à la fin du sondage.',
                ])
                ->add('published', null, [
                    'label' => 'Activé',
                    'required' => false,
                ])
                ->add('alertEnabled', CheckboxType::class, [
                    'label' => 'Afficher l’alerte',
                    'required' => false,
                    'help' => 'Masque l’alerte du sondage dans l’application pendant la période de vote.',
                ])
                ->add('participantCountThreshold', IntegerType::class, [
                    'label' => 'Seuil d’affichage des participants',
                    'attr' => ['min' => 0],
                    'help' => 'Les résultats restent masqués tant que ce nombre de participations n’est pas atteint.',
                ])
                ->add('resultDisplayMode', EnumType::class, [
                    'label' => 'Affichage des résultats',
                    'class' => PollResultDisplayModeEnum::class,
                    'choice_label' => static fn (PollResultDisplayModeEnum $mode): string => $mode->getLabel(),
                ])
            ->end()
        ;
    }
}
