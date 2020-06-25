<?php

namespace App\Admin\VotingPlatform;

use App\Entity\VotingPlatform\Designation\Designation;
use App\Form\Admin\DesignationTypeType;
use App\Form\Admin\DesignationZoneType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
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
            ->with('Général 📜', ['box_class' => 'box box-solid box-primary'])
                ->add('type', DesignationTypeType::class, [
                    'label' => 'Type d’élection',
                ])
                ->add('zones', DesignationZoneType::class, [
                    'label' => 'Zone',
                    'multiple' => true,
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
                    'widget' => 'single_text',
                    'with_seconds' => true,
                    'attr' => [
                        'step' => 1,
                    ],
                ])
                ->add('voteEndDate', DateTimeType::class, [
                    'label' => 'Clôture du vote',
                    'widget' => 'single_text',
                    'with_seconds' => true,
                    'attr' => [
                        'step' => 1,
                    ],
                ])
            ->end()
            ->with('Autre')
                ->add('additionalRoundDuration', IntegerType::class, ['label' => 'Durée du tour bis (jours)', 'attr' => ['min' => 1]])
                ->add('lockPeriodThreshold', IntegerType::class, ['label' => 'Le seuil (en jour) de démarrage de la période de réserve avant la fermeture des candidatures', 'attr' => ['min' => 1]])
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
            ->add('type', 'trans', ['format' => 'voting_platform.designation.type_%s'])
            ->add('zones', 'array')
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
