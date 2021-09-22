<?php

namespace Tests\App\Controller\Api\AdherentFilter;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadClientData;
use App\OAuth\Model\GrantTypeEnum;
use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

class GetFilterCollectionControllerTest extends AbstractWebCaseTest
{
    use ApiControllerTestTrait;
    use ControllerTestTrait;

    private const URI = '/api/v3/adherents/filters';

    public function testFilterEndpointSendFiltersCollectionSuccessfully(): void
    {
        $accessToken = $this->getAccessToken(
            LoadClientData::CLIENT_12_UUID,
            'BHLfR-MWLVBF@Z.ZBh4EdTFJ',
            GrantTypeEnum::PASSWORD,
            null,
            'referent@en-marche-dev.fr',
            LoadAdherentData::DEFAULT_PASSWORD
        );

        $this->client->request(Request::METHOD_GET, self::URI.'?scope=referent&feature=contacts', [], [], ['HTTP_AUTHORIZATION' => "Bearer $accessToken"]);

        self::assertJsonStringEqualsJsonString(
            <<<JSON
                [
                    {
                        "code": "gender",
                        "label": "Genre",
                        "options": {
                            "choices": {
                                "female": "Femme",
                                "male": "Homme",
                                "other": "Autre"
                            }
                        },
                        "type": "select"
                    },
                    {
                        "code": "firstName",
                        "label": "Prénom",
                        "options": null,
                        "type": "text"
                    },
                    {
                        "code": "lastName",
                        "label": "Nom",
                        "options": null,
                        "type": "text"
                    },
                    { 
                        "code": "age",
                        "label": "Âge",
                        "options": null,
                        "type": "integer_interval"
                    },
                    {
                        "code": "registered",
                        "label": "Adhésion",
                        "options": null,
                        "type": "date_interval"
                    },
                    {
                        "code": "zones",
                        "label": "Zone géographique",
                        "options": {
                            "url": "\/api\/v3\/zone\/autocompletion",
                            "query_param": "q",
                            "value_param": "uuid",
                            "label_param": "name"
                        },
                        "type": "autocomplete"
                    },
                    {
                        "code": "isCommitteeMember",
                        "label": "Membre d'un comité",
                        "options": {
                            "choices": {
                                "": "Tous",
                                "1": "Oui",
                                "0": "Non"
                            }
                        },
                        "type": "select"
                    },
                    {
                        "code": "isCertified",
                        "label": "Certifié",
                        "options": {
                            "choices": {
                                "": "Tous",
                                "1": "Oui",
                                "0": "Non"
                            }
                        },
                        "type": "select"
                    },
                    {
                        "code": "emailSubscription",
                        "label": "Abonné email",
                        "options": {
                            "choices": {
                                "": "Tous",
                                "1": "Oui",
                                "0": "Non"
                            }
                        },
                        "type": "select"
                    },
                    {
                        "code": "smsSubscription",
                        "label": "Abonné SMS",
                        "options": {
                            "choices": {
                                "": "Tous",
                                "1": "Oui",
                                "0": "Non"
                            }
                        },
                        "type": "select"
                    }
                ]
JSON,
            $this->client->getResponse()->getContent()
        );
    }
}
