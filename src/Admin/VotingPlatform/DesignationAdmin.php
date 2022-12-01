<?php

namespace App\Admin\VotingPlatform;

use App\Admin\AbstractAdmin;
use App\Entity\Geo\Zone;
use App\Entity\ReferentTag;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Form\Admin\DesignationGlobalZoneType;
use App\Form\Admin\DesignationTypeType;
use App\Form\Admin\VotingPlatform\DesignationNotificationType;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin as SonataAbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @param Designation
 */
class DesignationAdmin extends AbstractAdmin
{
    public const FORM_TYPE_LOCAL_ELECTION = 'form_type_local_election';

    protected function configureFormFields(FormMapper $form): void
    {
        /** @var Designation $subject */
        $subject = $this->getSubject();

        $formType = $form->getFormBuilder()->getOption('form_type');

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
                        'disabled' => $subject->isLocalElectionType(),
                        'choices' => [
                            Designation::DENOMINATION_DESIGNATION => Designation::DENOMINATION_DESIGNATION,
                            Designation::DENOMINATION_ELECTION => Designation::DENOMINATION_ELECTION,
                        ],
                    ])
                ->end()
                ->with('Zone 🌍', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-primary'])
                    ->add('globalZones', DesignationGlobalZoneType::class, [
                        'required' => false,
                        'label' => 'Zones globales',
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
                    ->add('zones', ModelAutocompleteType::class, [
                        'callback' => [$this, 'prepareZoneAutocompleteCallback'],
                        'property' => [
                            'name',
                            'code',
                        ],
                        'label' => 'Zones locales',
                        'multiple' => true,
                        'help' => 'Obligatoire pour les élections locales',
                        'btn_add' => false,
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
                        'help' => 'en jours, la valeur 0 désactive l\'affichage des résultats',
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

        $form->getFormBuilder()->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $formEvent) {
            $designationData = $formEvent->getData();

            if (DesignationTypeEnum::LOCAL_ELECTION === ($designationData['type'] ?? null)) {
                $designationData['denomination'] = Designation::DENOMINATION_ELECTION;
                $formEvent->setData($designationData);
            }
        });

        if ($formType && ($mainFields = $this->getMainFieldsForFormType($formType))) {
            foreach ($form->keys() as $key) {
                if (!\in_array($key, $mainFields)) {
                    $form->remove($key);
                }
            }
        }
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('label')
            ->add('type', null, [
                'field_type' => DesignationTypeType::class,
                'show_filter' => true,
            ])
            ->add('globalZones', null, [
                'field_type' => DesignationGlobalZoneType::class,
                'label' => 'Zones globales',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('id', null, ['label' => '#'])
            ->add('label')
            ->add('type', 'trans', ['format' => 'voting_platform.designation.type_%s'])
            ->add('zones', 'array', ['label' => 'Zones', 'virtual_field' => true, 'template' => 'admin/designation/list_zone.html.twig'])
            ->add('candidacyStartDate', null, ['label' => 'Ouverture des candidatures'])
            ->add('candidacyEndDate', null, ['label' => 'Clôture des candidatures'])
            ->add('voteStartDate', null, ['label' => 'Ouverture du vote'])
            ->add('voteEndDate', null, ['label' => 'Clôture du vote'])
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'edit' => [],
                ],
            ])
        ;
    }

    public function toString(object $object): string
    {
        return 'Désignation';
    }

    public static function prepareZoneAutocompleteCallback(
        SonataAbstractAdmin $admin,
        array $properties,
        string $value
    ): void {
        /** @var QueryBuilder $qb */
        $qb = $admin->getDatagrid()->getQuery();
        $alias = $qb->getRootAliases()[0];

        $orx = $qb->expr()->orX();
        foreach ($properties as $property) {
            $orx->add($alias.'.'.$property.' LIKE :property_'.$property);
            $qb->setParameter('property_'.$property, '%'.$value.'%');
        }
        $qb
            ->orWhere($orx)
            ->andWhere(sprintf('%1$s.type IN(:types) AND %1$s.active = 1', $alias))
            ->setParameter('types', [Zone::DEPARTMENT])
        ;
    }

    private function getMainFieldsForFormType(string $formType): array
    {
        if (self::FORM_TYPE_LOCAL_ELECTION === $formType) {
            return [
                'label',
                'zones',
                'voteStartDate',
                'voteEndDate',
            ];
        }

        return [];
    }
}
