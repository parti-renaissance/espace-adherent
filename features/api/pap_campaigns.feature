@api
Feature:
  In order to see PAP campaigns
  As a non logged-in user
  I should be able to access API PAP campaigns

  Scenario Outline: As a non logged-in user I cannot get and manage PAP campaigns
    Given I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method  | url                                                         |
      | GET     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9  |
      | GET     | /api/v3/pap_campaigns                                       |

  Scenario Outline: As a JeMarche App user I can not get not active PAP campaigns
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 404
    Examples:
      | method  | url                                                           |
      | GET     | /api/v3/pap_campaigns/932d67d1-2da6-4695-82f6-42afc20f2e41    |
      | GET     | /api/v3/pap_campaigns/9ba6b743-5018-4358-bdc0-eb2094010beb    |

  Scenario: As a JeMarche App user I cannot update not my PAP campaign
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "PUT" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da"
    Then the response status code should be 403

  Scenario: As a logged-in user I can get active PAP campaigns
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/pap_campaigns"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        {
            "title": "Campagne de 10 jours suivants",
            "brief": "**Campagne** de 10 jours suivants",
            "goal": 600,
            "finish_at": "@string@.isDateTime()",
            "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
        },
        {
            "title": "Campagne de 5 jours suivants",
            "brief": "**Campagne** de 5 jours suivants",
            "goal": 500,
            "finish_at": "@string@.isDateTime()",
            "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024"
        }
    ]
    """

  Scenario: As a logged-in user I can get all PAP campaigns
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/pap_campaigns?scope=pap_national_manager&pagination=true&page_size=5"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 5,
            "items_per_page": 5,
            "count": 5,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "title": "Campagne de 10 jours suivants",
                "brief": "**Campagne** de 10 jours suivants",
                "goal": 600,
                "finish_at": "@string@.isDateTime()",
                "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
            },
            {
                "title": "Campagne de 5 jours suivants",
                "brief": "**Campagne** de 5 jours suivants",
                "goal": 500,
                "finish_at": "@string@.isDateTime()",
                "uuid": "1c67b6bd-6da9-4a72-a266-813c419e7024"
            },
            {
                "title": "Campagne dans 10 jours",
                "brief": "### Campagne dans 10 jours",
                "goal": 400,
                "finish_at": "@string@.isDateTime()",
                "uuid": "63460047-c81a-44b9-aec9-152ecf58df93"
            },
            {
                "title": "Campagne dans 20 jours",
                "brief": "### Campagne dans 20 jours",
                "goal": 400,
                "finish_at": "@string@.isDateTime()",
                "uuid": "932d67d1-2da6-4695-82f6-42afc20f2e41"
            },
            {
                "title": "Campagne terminé",
                "brief": null,
                "goal": 100,
                "finish_at": "@string@.isDateTime()",
                "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb"
            }
        ]
    }
    """

  Scenario: As a logged-in user I can get one PAP campaign
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "title": "Campagne de 10 jours suivants",
        "brief": "**Campagne** de 10 jours suivants",
        "goal": 600,
        "finish_at": "@string@.isDateTime()",
        "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
    }
    """

  Scenario: As a logged-in user I can get passed PAP campaign
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/pap_campaigns/9ba6b743-5018-4358-bdc0-eb2094010beb?scope=pap_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "title": "Campagne terminé",
        "brief": null,
        "goal": 100,
        "finish_at": "@string@.isDateTime()",
        "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb"
    }
    """

  Scenario: As a logged-in user with no correct rights I cannot get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    And I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey"
    Then the response status code should be 403

  Scenario: As a logged-in user with correct rights I can get a campaign survey
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    And I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "id": @integer@,
      "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
      "type": "national",
      "questions":[
        {
          "id": @integer@,
          "type": "simple_field",
          "content": "Une première question du 1er questionnaire national ?",
          "choices": []
        },
        {
          "id": @integer@,
          "type": "multiple_choice",
          "content": "Une deuxième question du 1er questionnaire national ?",
          "choices": [
            {
              "id": @integer@,
              "content":"Réponse nationale A"
            },
            {
              "id": @integer@,
              "content":"Réponse nationale B"
            },
            {
              "id": @integer@,
              "content":"Réponse nationale C"
            },
            {
              "id": @integer@,
              "content":"Réponse nationale D"
            }
          ]
        }
      ],
      "name": "Questionnaire national numéro 1"
    }
    """

  Scenario: As a logged-in user with correct rights I can get a campaign survey config
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    And I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey-config"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "before_survey": {
            "address": [
                {
                    "code": "building_block",
                    "label": "Batiment",
                    "type": "text"
                },
                {
                    "code": "floor",
                    "label": "Étage",
                    "type": "number"
                },
                {
                    "code": "door",
                    "label": "Porte",
                    "type": "text"
                }
            ],
            "door_status": [
                [
                    {
                        "code": "door_open",
                        "label": "Porte ouverte"
                    },
                    {
                        "code": "door_closed",
                        "label": "Porte fermée"
                    }
                ]
            ],
            "response_status": [
                [
                    {
                        "code": "accept_to_answer",
                        "label": "Accepte de répondre aux questions"
                    },
                    {
                        "code": "dont_accept_to_answer",
                        "label": "N'accepte pas"
                    },
                    {
                        "code": "contact_later",
                        "label": "Repasser plus tard"
                    }
                ]
            ]
        },
        "after_survey": [
            [
                {
                    "code": "gender",
                    "label": "Genre",
                    "type": "choice",
                    "choices": {
                        "female": "Femme",
                        "male": "Homme"
                    }
                },
                {
                    "code": "age_range",
                    "label": "Tranche d'âge",
                    "type": "choice",
                    "choices": {
                        "less_than_20": "-20 ans",
                        "between_20_24": "20-24 ans",
                        "between_25_39": "25-39 ans",
                        "between_40_54": "40-54 ans",
                        "between_55_64": "55-64 ans",
                        "between_65_80": "65-80 ans",
                        "greater_than_80": "80+ ans"
                    }
                },
                {
                    "code": "profession",
                    "label": "Métier",
                    "type": "choice",
                    "choices": {
                        "employees": "Employé",
                        "workers": "Ouvrier",
                        "managerial staff": "Cadre",
                        "intermediate_professions": "Profession intermédiaire",
                        "self_contractor": "Indépendant et professions libérales",
                        "retirees": "Retraité",
                        "student": "Étudiant"
                    }
                }
            ],
            {
                "to_contact": {
                    "code": "to_contact",
                    "label": "Souhaite être recontacté ?",
                    "type": "boolean"
                },
                "contact": [
                    {
                        "code": "first_name",
                        "label": "Prénom",
                        "type": "text"
                    },
                    {
                        "code": "last_name",
                        "label": "Nom",
                        "type": "text"
                    },
                    {
                        "code": "email_address",
                        "label": "Email",
                        "type": "text"
                    }
                ]
            },
            [
                {
                    "code": "to_join",
                    "label": "Souhaite adhérer ?",
                    "type": "boolean"
                }
            ]
        ]
    }
    """

  Scenario: As a logged-in user I can get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    And I send a "GET" request to "/api/v3/pap_campaigns/tutorial"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "content": "**Texte du tutoriel** pour la *campagne* de PAP avec le Markdown"
    }
    """

  Scenario: As a logged-in user I cannot post a pap campaign history with wrong data
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaign_histories" with body:
    """
    {
        "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
        "status": "invalid"
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "building: Cette valeur ne doit pas être nulle.\nstatus: Le statut n'est pas valide.",
        "violations": [
            {
                "propertyPath": "building",
                "message": "Cette valeur ne doit pas être nulle."
            },
            {
                "propertyPath": "status",
                "message": "Le statut n'est pas valide."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can post a pap campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaign_histories" with body:
    """
    {
        "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
        "building": "0b81ff3d-f895-4e3f-bf6d-ff2a659c1c6f",
        "status": "door_closed",
        "building_block": "A",
        "floor": 1,
        "door": "3"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "status": "door_closed"
    }
    """

  Scenario: As a logged-in user I can update my pap campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da" with body:
    """
    {
        "status": "accept_to_answer",
        "building": "0b81ff3d-f895-4e3f-bf6d-ff2a659c1c6f",
        "building_block": "C",
        "floor": 2,
        "door": "23",
        "firstName": "Maria",
        "lastName": "Curei",
        "emailAddress": "maria.curie@test.com",
        "gender": "female",
        "ageRange": "between_40_54",
        "profession": "self_contractor",
        "toContact": true,
        "toJoin": false
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "uuid": "6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da",
        "status": "accept_to_answer"
    }
    """

  Scenario: As a logged-in user I can post a pap campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaign_histories" with body:
    """
    {
        "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9",
        "building": "0b81ff3d-f895-4e3f-bf6d-ff2a659c1c6f",
        "status": "door_closed",
        "building_block": "A",
        "floor": 1,
        "door": "3"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "uuid": "@uuid@",
        "status": "door_closed"
    }
    """

  Scenario: As a logged-in user I can update a pap campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da" with body:
    """
    {
        "status": "accept_to_answer",
        "building_block": "C",
        "floor": 2,
        "door": "23",
        "firstName": "Maria",
        "lastName": "Curei",
        "emailAddress": "maria.curie@test.com",
        "gender": "female",
        "ageRange": "between_40_54",
        "profession": "self_contractor",
        "toContact": true,
        "toJoin": false
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "uuid": "6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da",
        "status": "accept_to_answer"
    }
    """
