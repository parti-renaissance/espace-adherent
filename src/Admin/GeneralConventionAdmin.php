<?php

namespace App\Admin;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Form\Admin\SimpleMDEContent;
use App\GeneralConvention\MeetingTypeEnum;
use App\GeneralConvention\OrganizerEnum;
use App\GeneralConvention\ParticipantQuality;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class GeneralConventionAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('departmentZone', ModelAutocompleteType::class, [
                'multiple' => false,
                'label' => 'Département',
                'required' => true,
                'property' => ['name', 'code'],
                'callback' => function (AdminInterface $admin, array $property, $value): void {
                    $datagrid = $admin->getDatagrid();
                    $query = $datagrid->getQuery();
                    $rootAlias = $query->getRootAlias();
                    $query
                        ->andWhere($rootAlias.'.type = :type_department')
                        ->setParameter('type_department', Zone::DEPARTMENT)
                    ;

                    $datagrid->setValue($property[0], null, $value);
                },
                'btn_add' => false,
            ])
            ->add('committeeZone', ModelAutocompleteType::class, [
                'multiple' => false,
                'label' => 'Comité (zone)',
                'required' => false,
                'property' => ['name', 'code'],
                'callback' => function (AdminInterface $admin, array $property, $value): void {
                    $datagrid = $admin->getDatagrid();
                    $query = $datagrid->getQuery();
                    $rootAlias = $query->getRootAlias();
                    $query
                        ->andWhere($rootAlias.'.type = :type_city')
                        ->setParameter('type_city', Zone::CITY)
                    ;

                    $datagrid->setValue($property[0], null, $value);
                },
                'btn_add' => false,
            ])
            ->add('districtZone', ModelAutocompleteType::class, [
                'multiple' => false,
                'label' => 'Circonscription',
                'required' => false,
                'property' => ['name', 'code'],
                'callback' => function (AdminInterface $admin, array $property, $value): void {
                    $datagrid = $admin->getDatagrid();
                    $query = $datagrid->getQuery();
                    $rootAlias = $query->getRootAlias();
                    $query
                        ->andWhere($rootAlias.'.type = :type_district')
                        ->setParameter('type_district', Zone::DISTRICT)
                    ;

                    $datagrid->setValue($property[0], null, $value);
                },
                'btn_add' => false,
            ])
            ->add('organizer', EnumType::class, [
                'label' => 'Instance organisatrice',
                'class' => OrganizerEnum::class,
                'choice_label' => function (OrganizerEnum $organizer) {
                    return 'general_convention.organizer.'.$organizer->value;
                },
            ])
            ->add('reporter', ModelAutocompleteType::class, [
                'label' => 'Auteur de la remontée',
                'required' => true,
                'minimum_input_length' => 1,
                'items_per_page' => 20,
                'property' => [
                    'search',
                ],
                'to_string_callback' => static function (Adherent $adherent): string {
                    return \sprintf(
                        '%s (%s) [%s]',
                        $adherent->getFullName(),
                        $adherent->getEmailAddress(),
                        $adherent->getId()
                    );
                },
                'btn_add' => false,
            ])
            ->add('reportedAt', null, [
                'label' => 'Date de la remontée',
                'widget' => 'single_text',
            ])
            ->add('meetingType', EnumType::class, [
                'label' => 'Type de réunion',
                'class' => MeetingTypeEnum::class,
                'choice_label' => function (MeetingTypeEnum $meetingType) {
                    return 'general_convention.meeting_type.'.$meetingType->value;
                },
            ])
            ->add('membersCount', null, [
                'label' => 'Nombre de participants',
            ])
            ->add('participantQuality', EnumType::class, [
                'label' => 'Qualité des participants',
                'class' => ParticipantQuality::class,
                'choice_label' => function (ParticipantQuality $participantQuality) {
                    return 'general_convention.participant_quality.'.$participantQuality->value;
                },
            ])
            ->add('generalSummary', SimpleMDEContent::class, [
                'label' => 'Synthèse générale',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('partyDefinitionSummary', SimpleMDEContent::class, [
                'label' => 'Synthèse des réponses concernant l\'échange sur ce qu\'est un parti politique pour les participants',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('uniquePartySummary', SimpleMDEContent::class, [
                'label' => 'Synthèse des échanges sur "Renaissance, un parti pas comme les autres"',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('progressSince2016', SimpleMDEContent::class, [
                'label' => 'Synthèse des échanges sur le chemin parcouru depuis 2016',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('partyObjectives', SimpleMDEContent::class, [
                'label' => 'Synthèse des échanges sur les objectifs de Renaissance',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('governance', SimpleMDEContent::class, [
                'label' => 'Notre gouvernance',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('communication', SimpleMDEContent::class, [
                'label' => 'Notre communication',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('militantTraining', SimpleMDEContent::class, [
                'label' => 'La formation militante',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('memberJourney', SimpleMDEContent::class, [
                'label' => 'Le parcours adhérent',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('mobilization', SimpleMDEContent::class, [
                'label' => 'La mobilisation',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('talentDetection', SimpleMDEContent::class, [
                'label' => 'Détecter les talents',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('electionPreparation', SimpleMDEContent::class, [
                'label' => 'Préparer les élections',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('relationshipWithSupporters', SimpleMDEContent::class, [
                'label' => 'Notre relation aux sympathisants, aux corps intermédiaires, à la société civile',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('workWithPartners', SimpleMDEContent::class, [
                'label' => 'Notre travail avec les partenaires',
                'required' => false,
                'attr' => ['rows' => 10],
                'help_html' => true,
            ])
            ->add('additionalComments', SimpleMDEContent::class, [
                'label' => 'Souhaitez-vous ajouter quelque chose ?',
                'required' => false,
                'attr' => ['rows' => 10],
                'help' => 'help.markdown',
                'help_html' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('departmentZone', null, [
                'label' => 'Département',
            ])
            ->add('organizer', null, [
                'label' => 'Instance organisatrice',
                'template' => 'admin/general_convention/list_organizer.html.twig',
            ])
            ->add('district_or_committee', null, [
                'label' => 'Circonscription ou Comité organisateur',
                'virtual_field' => true,
                'template' => 'admin/general_convention/list_district_or_committee.html.twig',
            ])
            ->add('reportedAt', null, [
                'label' => 'Date',
            ])
            ->add('membersCount', null, [
                'label' => 'Participants',
            ])
            ->add('reporter', null, [
                'label' => 'Auteur',
            ])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }
}
