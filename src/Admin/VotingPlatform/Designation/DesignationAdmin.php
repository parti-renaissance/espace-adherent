<?php

declare(strict_types=1);

namespace App\Admin\VotingPlatform\Designation;

use App\Admin\AbstractAdmin;
use App\Entity\Geo\Zone;
use App\Entity\VotingPlatform\Designation\CandidacyPool\CandidacyPool;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Form\Admin\AdminZoneAutocompleteType;
use App\Form\Admin\DesignationGlobalZoneType;
use App\Form\Admin\DesignationTypeType;
use App\Form\Admin\SimpleMDEContent;
use App\Form\Admin\VotingPlatform\DesignationNotificationType;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\CollectionType;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\Form\Type\BooleanType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
                    ->add('type', DesignationTypeType::class, [
                        'label' => 'Type',
                        'disabled' => !$this->isCreation(),
                        'help_html' => true,
                        'choice_attr' => function ($choice, $label, $value) {
                            return [
                                'data-help' => match ($value) {
                                    DesignationTypeEnum::LOCAL_ELECTION => DesignationTypeEnum::TITLES[DesignationTypeEnum::LOCAL_ELECTION].', attachée à une zone (dpt), nécessite la <a href="'.$this->getRouteGenerator()->generate('admin_app_localelection_candidaciesgroup_list').'" target="_blank">création des listes de candidatures</a>. Seulement les adhérents du département dont le compte a été créé avant la date de début du vote (ou avant la date d\'initialisation de l\'élection si elle est renseignée) pourront voter.',
                                    DesignationTypeEnum::TERRITORIAL_ANIMATOR => DesignationTypeEnum::TITLES[DesignationTypeEnum::TERRITORIAL_ANIMATOR].', attachée à une zone (dpt), nécessite la <a href="'.$this->getRouteGenerator()->generate('admin_app_votingplatform_designation_candidacypool_candidacypool_list').'" target="_blank">création des listes de candidatures</a>. Seulement les adhérents du département à jour de cotisation et dont la date de la dernière cotisation est antérieure à la date limite pourront voter.',
                                    default => '',
                                },
                            ];
                        },
                        'help' => '<div id="type-help" class="help-block text-muted"></div>
                            <script>
                                $(function() {
                                    var $select = $("#'.$this->getUniqid().'_type");
                                    var $help = $("#type-help");

                                    function updateHelp() {
                                        $help.html($select.find("option:selected").data("help") || "");
                                    }

                                    $select.on("change", updateHelp);
                                    updateHelp();
                                });
                            </script>',
                    ])
                    ->add('label', null, [
                        'label' => 'Label',
                        'help' => 'Visible uniquement sur l\'interface d\'administration.',
                    ])
                    ->add('customTitle', null, [
                        'label' => 'Titre',
                        'required' => false,
                        'help' => 'Optionnel, un titre par défaut en fonction du type de la designation sera affiché',
                    ])
                    ->add('denomination', ChoiceType::class, [
                        'label' => 'Dénomination',
                        'disabled' => !$subject->isDenominationEditable(),
                        'choices' => [
                            Designation::DENOMINATION_DESIGNATION => Designation::DENOMINATION_DESIGNATION,
                            Designation::DENOMINATION_ELECTION => Designation::DENOMINATION_ELECTION,
                        ],
                    ])
                ->end()
                ->with('Zone & personnes concernées', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-primary'])
                    ->add('globalZones', DesignationGlobalZoneType::class, [
                        'required' => false,
                        'label' => 'Zones globales',
                        'multiple' => true,
                        'help' => 'pour les élections de types: "Comités-Adhérents" ou "Comités-Animateurs"',
                    ])
                    ->add('zones', AdminZoneAutocompleteType::class, [
                        'label' => 'Zones locales',
                        'multiple' => true,
                        'help' => 'Obligatoire pour l\'élection départementale et animateur territorial',
                        'btn_add' => false,
                        'zone_types' => [Zone::DEPARTMENT, Zone::FOREIGN_DISTRICT],
                    ])
                    ->add('targetYear', ChoiceType::class, [
                        'required' => false,
                        'label' => 'Collège électoral : Adhérent à jour à partir de :',
                        'choices' => array_combine($years = range(2022, date('Y')), $years),
                        'help' => 'Utilisé pour les élections de types : "Consultation nationale", "Vote", "Animateur territorial"',
                    ])
                    ->add('membershipDeadline', DateTimeType::class, [
                        'label' => 'Date limite de cotisation',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => false,
                        'attr' => ['step' => 30],
                    ])
                    ->add('accountCreationDeadline', DateTimeType::class, [
                        'label' => 'Date limite de création de compte',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => ['step' => 30],
                    ])
                ->end()
                ->with('Candidature 🎎', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-default'])
                    ->add('candidacyStartDate', DateTimeType::class, [
                        'label' => 'Ouverture des candidatures',
                        'widget' => 'single_text',
                        'required' => false,
                        'with_seconds' => true,
                        'attr' => ['step' => 30],
                        'disabled' => !$subject->isCandidacyPeriodEnabled(),
                    ])
                    ->add('candidacyEndDate', DateTimeType::class, [
                        'label' => 'Clôture des candidatures',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => ['step' => 30],
                        'disabled' => !$subject->isCandidacyPeriodEnabled(),
                    ])
                ->end()
                ->with('Date d\'initialisation de l\'élection', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-default', 'description' => 'La date à laquelle l\'élection est créée et le corps électoral est figé.<br/>Si vide, c\'est la date du vote qui sera prise.'])
                    ->add('electionCreationDate', DateTimeType::class, [
                        'label' => 'Date d\'initialisation',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => ['step' => 30],
                    ])
                ->end()
                ->with('Vote 🗳', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-default'])
                    ->add('voteStartDate', DateTimeType::class, [
                        'label' => 'Ouverture du vote',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => ['step' => 30],
                        'disabled' => !$this->isCreation(),
                    ])
                    ->add('voteEndDate', DateTimeType::class, [
                        'label' => 'Clôture du vote',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => ['step' => 30],
                    ])
                ->end()
            ->end()
            ->tab('Notifications 📯')
                ->with('Envoi d\'email')
                    ->add('notifications', DesignationNotificationType::class, ['label' => false, 'required' => false])
                    ->add('notifyPotentialElectorate', CheckboxType::class, [
                        'label' => 'Notifier le collège électoral potentiel',
                        'required' => false,
                        'help' => 'Uniquement pour Consultation/Vote avec un "Collège électoral" (année) défini. Si coché, le mail d\'ouverture du vote et les rappels J-1/H-1 sont envoyés à tous les adhérents Renaissance dans le périmètre (à jour ou non), avec un CTA "Me mettre à jour" pour les non-à-jour. L\'annonce J-2 et le mail de résultats ne sont PAS affectés.',
                    ])
                ->end()
            ->end()
            ->tab('Questionnaire ❓')
                ->with('Questionnaire')
                    ->add('poll', ModelType::class, [
                        'label' => false,
                        'required' => false,
                        'btn_add' => 'Créer',
                    ])
                    ->add('enableVoteQuestionsPreview', null, ['label' => 'Afficher le questionnaire avant le vote'])
                ->end()
            ->end()
            ->tab('Candidatures 🎎')
                ->with('Candidatures')
                    ->add('candidacyPools', CollectionType::class, [
                        'label' => false,
                        'required' => false,
                        'by_reference' => false,
                        'allow_add' => true,
                        'allow_delete' => true,
                        'entry_type' => ModelType::class,
                        'entry_options' => [
                            'class' => CandidacyPool::class,
                            'model_manager' => $this->getModelManager(),
                        ],
                    ])
                ->end()
            ->end()
            ->tab('Wording 🌐')
                ->with('Description de l\'élection', ['class' => 'col-md-6'])
                    ->add('wordingWelcomePage', ModelType::class, [
                        'label' => false,
                        'required' => false,
                        'btn_add' => 'Créer',
                    ])
                ->end()
                ->with('Règlement', ['class' => 'col-md-6'])
                    ->add('wordingRegulationPage', ModelType::class, [
                        'label' => false,
                        'required' => false,
                        'btn_add' => 'Créer',
                    ])
                ->end()
                ->with('Alerte', ['class' => 'col-md-6', 'description' => 'Affichée sur le fil d\'actualité'])
                    ->add('alertTitle', TextType::class, [
                        'required' => false,
                        'label' => 'Titre',
                    ])
                    ->add('alertCtaLabel', TextType::class, [
                        'required' => false,
                        'label' => 'CTA Label',
                    ])
                    ->add('alertBeginAt', DateTimeType::class, [
                        'label' => 'Date d\'affichage de l\'alerte',
                        'required' => false,
                        'widget' => 'single_text',
                        'with_seconds' => true,
                        'attr' => ['step' => 30],
                    ])
                    ->add('alertDescription', SimpleMDEContent::class, [
                        'required' => false,
                        'label' => 'Description',
                        'help' => 'help.markdown',
                        'help_html' => true,
                    ])
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
                    ->add('resultDisplayBlank', null, [
                        'label' => 'Afficher "Ne se prononce pas"',
                        'required' => false,
                        'help' => 'Si le vote blanc est activé',
                    ])
                ->end()
                ->with('Scrutin proportionnel plurinominal', ['class' => 'col-md-6', 'box_class' => 'box box-solid box-info'])
                    ->add('seats', NumberType::class, [
                        'required' => false,
                        'label' => 'Sièges',
                        'attr' => ['min' => 1, 'step' => 1],
                        'help' => 'Le nombre de sièges à attribuer',
                        'html5' => true,
                    ])
                    ->add('majorityPrime', NumberType::class, [
                        'required' => false,
                        'label' => 'Prime majoritaire',
                        'attr' => ['min' => 1, 'step' => 1],
                        'help' => 'en %',
                        'html5' => true,
                    ])
                    ->add('majorityPrimeRoundSupMode', CheckboxType::class, [
                        'required' => false,
                        'label' => 'Configurer l\'arrondi : vers l\'entier supérieur',
                        'help' => 'Si cochée l\'arrondi sera vers l\'entier supérieur, sinon inférieur',
                    ])
                ->end()
            ->end()
            ->tab('Autre ⚙')
                ->with('Vote', ['class' => 'col-md-6'])
                    ->add('isBlankVoteEnabled', BooleanType::class, [
                        'transform' => true,
                        'label' => 'Le vote blanc activé',
                        'disabled' => !$subject->isBlankVoteAvailable(),
                    ])
                ->end()
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
            ->addIdentifier('label', null, ['virtual_field' => true, 'template' => 'admin/designation/label.html.twig'])
            ->add('type', 'trans', ['format' => 'voting_platform.designation.type_%s'])
            ->add('zones', 'array', ['label' => 'Zones', 'virtual_field' => true, 'template' => 'admin/designation/list_zone.html.twig'])
            ->add('candidacyStartDate', null, ['label' => 'Ouverture des candidatures'])
            ->add('candidacyEndDate', null, ['label' => 'Clôture des candidatures'])
            ->add('electionCreationDate', null, ['label' => 'Date d\'initialisation'])
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
        return 'Désignation - '.$object->getUuid();
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
