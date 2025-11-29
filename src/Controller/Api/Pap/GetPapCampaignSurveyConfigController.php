<?php

declare(strict_types=1);

namespace App\Controller\Api\Pap;

use App\Jecoute\AgeRangeEnum;
use App\Jecoute\ProfessionEnum;
use App\Pap\CampaignHistoryStatusEnum;
use App\ValueObject\Genders;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')"))]
#[Route(path: '/v3/pap_campaigns/{uuid}/survey-config', name: 'api_get_pap_campaign_survey_config', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class GetPapCampaignSurveyConfigController extends AbstractController
{
    public function __invoke(): JsonResponse
    {
        return $this->json([
            'before_survey' => [
                'door_status' => self::transformStatusArray(CampaignHistoryStatusEnum::DOOR_STATUS, CampaignHistoryStatusEnum::DOOR_OPEN),
                'response_status' => self::transformStatusArray(CampaignHistoryStatusEnum::RESPONSE_STATUS, CampaignHistoryStatusEnum::ACCEPT_TO_ANSWER),
            ],
            'after_survey' => [
                [
                    'description' => 'Afin d’améliorer l’analyse des réponses à ce sondage vous pouvez renseigner le profil de votre interlocuteur. Toutes ces informations sont facultatives. ',
                    'questions' => [
                        $this->buildQuestion('gender', 'choice', 'Quelle est votre civilité ?', false, Genders::MALE_FEMALE_LABELS, 'single_row'),
                        $this->buildQuestion('age_range', 'choice', 'Sa tranche d\'âge', false, AgeRangeEnum::choices(), 'cols_2'),
                        $this->buildQuestion('profession', 'choice', 'Sa profession', false, ProfessionEnum::choices(), 'cols_1'),
                    ],
                ],
                [
                    'description' => null,
                    'questions' => [
                        $this->buildQuestion(
                            'to_contact',
                            'boolean',
                            'Souhaite-t-il être tenu au courant des résultats de cette consultation et recevoir notre actualité politique par email ?',
                            true,
                            null,
                            null,
                            null,
                            null,
                            'En cochant oui, vous certifiez qu\'il consent à ce que ses données personnelles soient traitées par Renaissance et qu\'il est informé des droits dont il dispose sur ses données - notamment, la possibilité de se désinscrire à tout moment.'),
                        $this->buildQuestion(
                            'profil',
                            'compound',
                            'Informations personnelles',
                            true,
                            [
                                $this->buildQuestion('first_name', 'text', 'Prénom', true, null, null, null, 'Indiquez ici le prénom de la personne rencontrée'),
                                $this->buildQuestion('last_name', 'text', 'Nom', true, null, null, null, 'Indiquez ici le nom de la personne rencontrée'),
                                $this->buildQuestion('email_address', 'text', 'Email', true, null, null, null, 'Indiquez ici l\'e-mail de la personne rencontrée'),
                            ],
                            null,
                            [
                                'question' => 'to_contact',
                                'choices' => [true],
                            ]
                        ),
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
        ?array $choicesOrChildren = null,
        ?string $widget = null,
        ?array $dependency = null,
        ?string $placeholder = null,
        ?string $help = null,
        array $questionOptions = [],
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

    private function transformStatusArray(array $statuses, string $successStatus): array
    {
        return array_map(function (string $code) use ($successStatus): array {
            return [
                'code' => $code,
                'label' => CampaignHistoryStatusEnum::LABELS[$code],
                'success_status' => $successStatus === $code,
            ];
        }, $statuses);
    }
}
