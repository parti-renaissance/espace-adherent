<?php

namespace App\Admin\VotingPlatform;

use App\Admin\AbstractAdmin;
use App\Entity\ReferentTag;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Form\Admin\DesignationTypeType;
use App\Form\Admin\DesignationZoneType;
use App\Form\Admin\VotingPlatform\DesignationNotificationType;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * @param Designation
 */
class DesignationAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->tab('Général 📜')
                ->with('Général 📜', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-primary'])
                    ->add('label', null, [
                        'label' => 'Label',
                    ])
                    ->add('type', DesignationTypeType::class, [
                        'label' => 'Type d’élection',
                        'disabled' => !$this->isCreation(),
                    ])
                    ->add('denomination', ChoiceType::class, [
                        'label' => 'Dénomination',
                        'choices' => [
                            Designation::DENOMINATION_DESIGNATION => Designation::DENOMINATION_DESIGNATION,
                            Designation::DENOMINATION_ELECTION => Designation::DENOMINATION_ELECTION,
                        ],
                    ])
                ->end()
                ->with('Zone 🌍', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-primary'])
                    ->add('zones', DesignationZoneType::class, [
                        'required' => false,
                        'label' => 'Zone',
                        'multiple' => true,
                        'help' => 'pour les élections de types: "Comités-Adhérents" ou "Comités-Animateurs"',
                    ])
                    ->add('referentTags', EntityType::class, [
                        'class' => ReferentTag::class,
                        'required' => false,
                        'label' => 'Référent tags',
                        'multiple' => true,
                        'help' => 'pour les élections de type "Copol"',
                        'attr' => [
                            'data-sonata-select2' => 'false',
                        ],
                    ])
                ->end()
                ->with('Candidature 🎎', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-default'])
                    ->add('candidacyStartDate', DateTimeType::class, [
                        'label' => 'Ouverture des candidatures',
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => [
                            'step' => 1,
                        ],
                    ])
                    ->add('candidacyEndDate', DateTimeType::class, [
                        'label' => 'Clôture des candidatures',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => [
                            'step' => 1,
                        ],
                    ])
                ->end()
                ->with('Vote 🗳', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-default'])
                    ->add('voteStartDate', DateTimeType::class, [
                        'label' => 'Ouverture du vote',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => [
                            'step' => 1,
                        ],
                    ])
                    ->add('voteEndDate', DateTimeType::class, [
                        'label' => 'Clôture du vote',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => [
                            'step' => 1,
                        ],
                    ])
                ->end()
            ->end()
            ->tab('Notifications 📯')
                ->with('Envoi de mail')
                    ->add('notifications', DesignationNotificationType::class, ['required' => false])
                ->end()
            ->end()
            ->tab('Résultats 🏆')
                ->with('Affichage', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-default'])
                    ->add('resultDisplayDelay', IntegerType::class, [
                        'label' => 'Durée d’affichage des résultats',
                        'attr' => ['min' => 0],
                        'help' => 'en jours',
                    ])
                    ->add('resultScheduleDelay', NumberType::class, [
                        'label' => 'Afficher les résultats au bout de :',
                        'attr' => ['min' => 0, 'step' => 0.5],
                        'help' => 'en heures',
                        'scale' => 1,
                        'html5' => true,
                        'required' => false,
                    ])
                ->end()
            ->end()
            ->tab('Autre ⚙️')
                ->with('Tour bis', ['class' => 'col-md-6'])
                    ->add('additionalRoundDuration', IntegerType::class, [
                        'label' => 'Durée du tour bis',
                        'attr' => ['min' => 1],
                        'help' => 'en jours',
                    ])
                ->end()
                ->with('Période de réserve', ['class' => 'col-md-6'])
                    ->add('lockPeriodThreshold', IntegerType::class, [
                        'label' => 'Le seuil de démarrage de la période de réserve avant la fermeture des candidatures',
                        'attr' => ['min' => 0],
                        'help' => 'en jours',
                    ])
                ->end()
            ->end()
        ;
    }

    public function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('type', null, [
                'field_type' => DesignationTypeType::class,
                'show_filter' => true,
            ])
            ->add('zones', null, [
                'field_type' => DesignationZoneType::class,
                'show_filter' => true,
            ])
        ;
    }

    public function configureListFields(ListMapper $list)
    {
        $list
            ->add('id', null, ['label' => '#'])
            ->add('label')
            ->add('type', 'trans', ['format' => 'voting_platform.designation.type_%s'])
            ->add('zones', 'array', ['template' => 'admin/designation/list_zone.html.twig'])
            ->add('candidacyStartDate', null, ['label' => 'Ouverture des candidatures'])
            ->add('candidacyEndDate', null, ['label' => 'Clôture des candidatures'])
            ->add('voteStartDate', null, ['label' => 'Ouverture du vote'])
            ->add('voteEndDate', null, ['label' => 'Clôture du vote'])
            ->add('_action', null, [
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    public function toString($object)
    {
        return 'Désignation';
    }
}
