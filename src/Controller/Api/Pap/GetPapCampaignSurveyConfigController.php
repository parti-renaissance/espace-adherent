<?php

namespace App\Controller\Api\Pap;

use App\Jecoute\AgeRangeEnum;
use App\Jecoute\ProfessionEnum;
use App\Pap\CampaignHistoryStatusEnum;
use App\Pap\CampaignHistoryVoterStatusEnum;
use App\ValueObject\Genders;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/pap_campaigns/{uuid}/survey-config", requirements={"uuid": "%pattern_uuid%"}, name="api_get_pap_campaign_survey_config", methods={"GET"})
 *
 * @Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')")
 */
class GetPapCampaignSurveyConfigController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        return $this->json([
            'before_survey' => [
                [
                    'description' => null,
                    'questions' => [
                        $this->buildQuestion('door_status', 'choice', 'La porte s\'ouvre t-elle ?', true, CampaignHistoryStatusEnum::DOOR_STATUS, null, null, null, null, ['success_choice' => CampaignHistoryStatusEnum::DOOR_OPEN]),
                    ],
                ],
                [
                    'description' => null,
                    'questions' => [
                        $this->buildQuestion('response_status', 'choice', 'Votre interlocuteur', true, CampaignHistoryStatusEnum::RESPONSE_STATUS, null, null, null, null, ['success_choice' => CampaignHistoryStatusEnum::ACCEPT_TO_ANSWER]),
                    ],
                ],
            ],
            'after_survey' => [
                [
                    'description' => 'Afin d’améliorer l’analyse des réponses à ce sondage vous pouvez renseigner le profil de votre interlocuteur. Toutes ces informations sont facultatives. ',
                    'questions' => [
                        $this->buildQuestion('gender', 'choice', 'Quel est votre genre', true, Genders::MALE_FEMALE_LABELS, 'single_row'),
                        $this->buildQuestion('age_range', 'choice', 'Sa tranche d\'âge', true, AgeRangeEnum::choices(), 'cols_2'),
                        $this->buildQuestion('profession', 'choice', 'Sa profession', true, ProfessionEnum::choices(), 'cols_1'),
                    ],
                ],
                [
                    'description' => null,
                    'questions' => [
                        $this->buildQuestion('voter_status', 'choice', 'Est-il inscrit sur les listes électorales ?', false, CampaignHistoryVoterStatusEnum::LABELS, 'cols_1'),
                        $this->buildQuestion('voter_postal_code', 'text', 'Quel est le code postal de la commune de vote ?', true, CampaignHistoryVoterStatusEnum::LABELS, null, ['question' => 'voter_status', 'choices' => [CampaignHistoryVoterStatusEnum::REGISTERED_ELSEWHERE]], 'Code postal'),
                    ],
                ],
                [
                    'description' => null,
                    'questions' => [
                        $this->buildQuestion('to_contact', 'boolean', 'Souhaite-t-il être au courant des résultats de cette consultation via e-mail ?', false, null, null, null, null, 'En cochant oui, vous certifiez qu\'il consent à ce que ses données personnelles soient traitées par La République En Marche dans le cadre de ce sondage et qu\'il est informé des droits dont il dispose sur ses données.'),
                        $this->buildQuestion(
                            'profil',
                            'compound',
                            'Informations personnelles',
                            true,
                            [
                                $this->buildQuestion('first_name', 'text', 'Prénom', true, null, null, null, 'Indiquez ici le prénom de la personne rencontrée'),
                                $this->buildQuestion('last_name', 'text', 'Nom', true, null, null, null, 'Indiquez ici le nom de la personne rencontrée'),
                                $this->buildQuestion('email_address', 'text', 'E-mail', true, null, null, null, 'Indiquez ici l\'e-mail de la personne rencontrée'),
                            ],
                            null,
                            [
                                'question' => 'to_contact',
                                'choices' => [true],
                            ]
                        ),
                        $this->buildQuestion('to_join', 'boolean', 'Souhaite adhérer ?', false, null, null, ['question' => 'to_contact', 'choices' => [true]], null, 'En cochant oui, vous certifiez qu\'il souhait adhérer.'),
                    ],
                ],
            ],
        ]);
    }

    private function buildQuestion(
        string $code,
        string $type,
        string $label,
        bool $required = false,
        array $choicesOrChildren = null,
        string $widget = null,
        array $dependency = null,
        string $placeholder = null,
        string $help = null,
        array $questionOptions = []
    ): array {
        $question = [
            'code' => $code,
            'type' => $type,
        ];
        $options = [
            'label' => $label,
            'required' => $required,
        ];

        if ($widget) {
            $options['widget'] = $widget;
        }

        if ($placeholder) {
            $options['placeholder'] = $placeholder;
        }

        if ($help) {
            $options['help'] = $help;
        }

        if ($dependency) {
            $question['dependency'] = $dependency;
        }

        if ('choice' === $type) {
            $options['multiple'] = false;

            if ($choicesOrChildren) {
                $options['choices'] = $choicesOrChildren;
            }
        } elseif ('compound' === $type) {
            if ($choicesOrChildren) {
                $options['children'] = $choicesOrChildren;
            }
        }

        $question['options'] = array_merge($options, $questionOptions);

        return $question;
    }
}
