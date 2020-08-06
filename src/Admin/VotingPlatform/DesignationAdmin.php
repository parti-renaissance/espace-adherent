<?php

namespace App\Admin\VotingPlatform;

use App\Entity\ReferentTag;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Form\Admin\DesignationTypeType;
use App\Form\Admin\DesignationZoneType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

/**
 * @param Designation
 */
class DesignationAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form)
    {
        $form
            ->with('Général 📜', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-primary'])
                ->add('label', null, [
                    'label' => 'Label',
                ])
                ->add('type', DesignationTypeType::class, [
                    'label' => 'Type d’élection',
                ])
            ->end()
            ->with('Zone 🌍', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-primary'])
                ->add('zones', DesignationZoneType::class, [
                    'required' => false,
                    'label' => 'Zone',
                    'multiple' => true,
                    'help' => 'pour les élections de type "Comités-Adhérents"',
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
            ->with('Autre')
                ->add('additionalRoundDuration', IntegerType::class, ['label' => 'Durée du tour bis (jours)', 'attr' => ['min' => 1]])
                ->add('lockPeriodThreshold', IntegerType::class, ['label' => 'Le seuil (en jour) de démarrage de la période de réserve avant la fermeture des candidatures', 'attr' => ['min' => 0]])
            ->end()
            ->with('Résultats 🏆', ['box_class' => 'box box-solid box-default'])
                ->add('resultDisplayDelay', IntegerType::class, ['label' => 'Durée d’affichage des résultats (jours)', 'attr' => ['min' => 0]])
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

    public function configureBatchActions($actions)
    {
        return [];
    }

    public function toString($object)
    {
        return 'Désignation';
    }
}
