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
        return $this->json(
            [
                'before_survey' => [
                    'address' => [
                        [
                            'code' => 'building_block',
                            'label' => 'Bâtiment',
                            'type' => 'text',
                        ],
                        [
                            'code' => 'floor',
                            'label' => 'Étage',
                            'type' => 'number',
                        ],
                        [
                            'code' => 'door',
                            'label' => 'Porte',
                            'type' => 'text',
                        ],
                    ],
                    'door_status' => self::transformStatusArray(CampaignHistoryStatusEnum::DOOR_STATUS),
                    'response_status' => self::transformStatusArray(CampaignHistoryStatusEnum::RESPONSE_STATUS),
                ],
                'after_survey' => [
                    [
                        [
                            'code' => 'gender',
                            'label' => 'Genre',
                            'type' => 'choice',
                            'choices' => Genders::MALE_FEMALE_LABELS,
                        ],
                        [
                            'code' => 'age_range',
                            'label' => 'Tranche d\'âge',
                            'type' => 'choice',
                            'choices' => AgeRangeEnum::choices(),
                        ],
                        [
                            'code' => 'profession',
                            'label' => 'Métier',
                            'type' => 'choice',
                            'choices' => ProfessionEnum::choices(),
                        ],
                    ],
                    [
                        'to_contact' => [
                            'code' => 'to_contact',
                            'label' => 'Souhaite être recontacté ?',
                            'description' => 'En cochant oui, vous certifiez qu\'il consent à ce que ses données personnelles soient traitées par La République En Marche dans le cadre de ce sondage et qu\'il est informé des droits dont il dispose sur ses données.',
                            'type' => 'boolean',
                        ],
                        'contact' => [
                            [
                                'code' => 'first_name',
                                'label' => 'Prénom',
                                'type' => 'text',
                            ],
                            [
                                'code' => 'last_name',
                                'label' => 'Nom',
                                'type' => 'text',
                            ],
                            [
                                'code' => 'email_address',
                                'label' => 'Email',
                                'type' => 'text',
                            ],
                        ],
                    ],
                    [
                        'voter_status' => [
                            'code' => 'voter_status',
                            'label' => 'Êtes vous un électeur inscrit sur les listes ?',
                            'type' => 'choice',
                            'choices' => CampaignHistoryVoterStatusEnum::LABELS,
                        ],
                        'voter_postal_code' => [
                            'code' => 'voter_postal_code',
                            'label' => 'Quel est le code postal de la commune de vote ?',
                            'type' => 'text',
                        ],
                    ],
                    [
                        [
                            'code' => 'to_join',
                            'label' => 'Souhaite adhérer ?',
                            'description' => 'En cochant oui, vous certifiez qu\'il souhait adhérer.',
                            'type' => 'boolean',
                        ],
                    ],
                ],
            ]
        );
    }

    private static function transformStatusArray(array $statuses): array
    {
        return array_map(function (string $code) {
            return [
                'code' => $code,
                'label' => CampaignHistoryStatusEnum::LABELS[$code],
            ];
        }, $statuses);
    }
}
