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
      | POST    | /api/v3/pap_campaigns                                       |
      | PUT     | /api/v3/pap_campaigns/9ba6b743-5018-4358-bdc0-eb2094010beb  |

  Scenario Outline: As a JeMarche App user I can not get not active PAP campaigns
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 404
    Examples:
      | method  | url                                                           |
      | GET     | /api/v3/pap_campaigns/932d67d1-2da6-4695-82f6-42afc20f2e41    |
      | GET     | /api/v3/pap_campaigns/9ba6b743-5018-4358-bdc0-eb2094010beb    |

  Scenario Outline: As a user with no correct rights I can not create or edit PAP campaign
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                                                   |
      # not mine
      | PUT     | /api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da   |
      # active
      | PUT     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9            |

  Scenario Outline: As a logged-in user with no PAP user role I cannot get and manage PAP campaigns
    Given I am logged with "deputy-75-2@en-marche-dev.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    And I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                                                       |
      | GET     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9                |
      | PUT     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9                |
      | GET     | /api/v3/pap_campaigns                                                     |
      | GET     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey         |
      | GET     | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/survey-config  |
      | GET     | /api/v3/pap_campaigns/tutorial                                            |

  Scenario Outline: As a logged-in user with no correct rights I cannot get PAP campaigns on DC
    Given I am logged with "benjyd@aol.com" via OAuth client "Data-Corner"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method | url                                                                                    |
      | GET    | /api/v3/pap_campaigns?scope=pap_national_manager                                       |
      | GET    | /api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9?scope=pap_national_manager  |
      | GET    | /api/v3/pap_campaigns/kpi?scope=pap_national_manager                                   |

  Scenario: As a JeMarche App user I cannot update not my PAP campaign
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "PUT" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da"
    Then the response status code should be 403

  Scenario: As a logged-in user I can get active PAP campaigns
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap_campaigns?pagination=false"
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
    When I send a "GET" request to "/api/v3/pap_campaigns?scope=pap_national_manager&page_size=5"
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
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
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
        "uuid": "9ba6b743-5018-4358-bdc0-eb2094010beb",
        "nb_surveys": 0,
        "nb_visited_doors": 0,
        "nb_collected_contacts": 0,
        "average_visit_time": 0
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
      "questions": [
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
                "label": "Bâtiment",
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
              {
                 "code": "door_open",
                 "label": "Porte ouverte"
              },
              {
                 "code": "door_closed",
                 "label": "Porte fermée"
              }
          ],
          "response_status": [
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
                "description": "En cochant oui, vous certifiez qu'il consent \u00e0 ce que ses données personnelles soient traitées par La République En Marche dans le cadre de ce sondage et qu'il est informé des droits dont il dispose sur ses données.",
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
          {
             "voter_status": {
                "code": "voter_status",
                "label": "Êtes vous un électeur inscrit sur les listes ?",
                "type": "choice",
                "choices": {
                   "not_voter": "Pas électeur",
                   "not_registered": "Pas inscrit",
                   "registered": "Inscrit sur les listes",
                   "registered_elsewhere": "Inscrit ailleurs"
                }
             },
             "voter_postal_code": {
                "code": "voter_postal_code",
                "label": "Quel est le code postal de la commune de vote ?",
                "type": "text"
             }
          },
          [
             {
                "code": "to_join",
                "label": "Souhaite adhérer ?",
                "description": "En cochant oui, vous certifiez qu'il souhait adhérer.",
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

  Scenario: As a logged-in user I cannot update a pap campaign history with invalid data
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaign_histories/6b3d2e20-8f66-4cbb-a7ce-2a1b740c75da" with body:
    """
    {
        "status": "invalid",
        "email_address": "01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ",
        "gender": "invalid",
        "age_range": "invalid",
        "profession": "invalid"
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "status: Le statut n'est pas valide.\nemail_address: Cette valeur n'est pas une adresse email valide.\nemail_address: L'adresse e-mail est trop longue, 255 caractères maximum.\ngender: Ce sexe n'est pas valide.\nage_range: Cette valeur doit être l'un des choix proposés.\nprofession: Cette valeur doit être l'un des choix proposés.",
        "violations": [
            {
                "propertyPath": "status",
                "message": "Le statut n'est pas valide."
            },
            {
                "propertyPath": "email_address",
                "message": "Cette valeur n'est pas une adresse email valide."
            },
            {
                "propertyPath": "email_address",
                "message": "L'adresse e-mail est trop longue, 255 caractères maximum."
            },
            {
                "propertyPath": "gender",
                "message": "Ce sexe n'est pas valide."
            },
            {
                "propertyPath": "age_range",
                "message": "Cette valeur doit être l'un des choix proposés."
            },
            {
                "propertyPath": "profession",
                "message": "Cette valeur doit être l'un des choix proposés."
            }
        ]
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
        "voter_status": "registered",
        "voter_postal_code": "92110",
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

  Scenario: As a logged-in user I can not create a campaign with no data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=pap_national_manager" with body:
    """
    {
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "title: Cette valeur ne doit pas être vide.\ngoal: Cette valeur ne doit pas être vide.\nsurvey: Cette valeur ne doit pas être vide.",
        "violations": [
            {
                "propertyPath": "title",
                "message": "Cette valeur ne doit pas être vide."
            },
            {
                "propertyPath": "goal",
                "message": "Cette valeur ne doit pas être vide."
            },
            {
                "propertyPath": "survey",
                "message": "Cette valeur ne doit pas être vide."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can not create a campaign with invalid data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=pap_national_manager" with body:
    """
    {
        "title": "Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP Nouvelle campagne PAP",
        "brief": "**NOUVEAU**",
        "goal": 0,
        "begin_at": "2022-05-01 00:00:00",
        "finish_at": "2022-05-31 00:00:00",
        "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "goal: Cette valeur doit être supérieure à \"0\".",
        "violations": [
            {
                "propertyPath": "goal",
                "message": "Cette valeur doit être supérieure à \"0\"."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can create a campaign
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/pap_campaigns?scope=pap_national_manager" with body:
    """
    {
        "title": "Nouvelle campagne PAP",
        "brief": "**NOUVEAU**",
        "goal": 200,
        "begin_at": "2022-05-01 00:00:00",
        "finish_at": "2022-05-31 00:00:00",
        "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
        "title": "Nouvelle campagne PAP",
        "brief": "**NOUVEAU**",
        "goal": 200,
        "begin_at": "2022-05-01T00:00:00+02:00",
        "finish_at": "2022-05-31T00:00:00+02:00",
        "survey": {
            "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
        },
        "uuid": "@uuid@"
    }
    """

  Scenario: As a logged-in user I can update a campaign
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9?scope=pap_national_manager" with body:
    """
    {
        "title": "NOUVEAU Campagne de 10 jours suivants",
        "brief": "NOUVEAU **Campagne** de 10 jours suivants",
        "goal": 1000,
        "begin_at": "2022-04-01 00:00:00",
        "finish_at": "2022-04-30 00:00:00",
        "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "title": "NOUVEAU Campagne de 10 jours suivants",
        "brief": "NOUVEAU **Campagne** de 10 jours suivants",
        "goal": 1000,
        "begin_at": "2022-04-01T00:00:00+02:00",
        "finish_at": "2022-04-30T00:00:00+02:00",
        "survey": {
            "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
        },
        "uuid": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
    }
    """

  Scenario: As a logged-in user I can get PAP campaign ranking
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMarche App" with scope "jemarche_app"
    When I send a "GET" request to "/api/v3/pap_campaigns/d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9/ranking"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        {
            "label": "Individuel",
            "fields": {
                "rank": "Rang",
                "questioner": "Militant",
                "nb_visited_doors": "Portes frappées",
                "nb_surveys": "Questionnaires remplis"
            },
            "items": [
                {
                    "rank": 1,
                    "questioner": "Patrick B.",
                    "nb_visited_doors": 1,
                    "nb_surveys": 1,
                    "current": false
                },
                {
                    "rank": 2,
                    "questioner": "Jacques P.",
                    "nb_visited_doors": 2,
                    "nb_surveys": 0,
                    "current": false
                },
                {
                    "rank": "...",
                    "questioner": "Referent R.",
                    "nb_visited_doors": 0,
                    "nb_surveys": 0,
                    "current": true
                }
            ]
        },
        {
            "label": "Département",
            "fields": {
                "rank": "Rang",
                "department": "Département",
                "nb_visited_doors": "Portes frappées",
                "nb_surveys": "Questionnaires remplis"
            },
            "items": [
                {
                    "rank": 1,
                    "department": "Paris 8ème",
                    "nb_visited_doors": 3,
                    "nb_surveys": 1,
                    "current": false
                },
                {
                    "rank": "...",
                    "department": "Seine-et-Marne",
                    "nb_visited_doors": 0,
                    "nb_surveys": 0,
                    "current": true
                }
            ]
        }
    ]
    """

  Scenario: As a DC PAP national manger I can get PAP campaigns KPI
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/pap_campaigns/kpi?scope=pap_national_manager"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "nb_campaigns": "5",
        "nb_ongoing_campaigns": "4",
        "nb_visited_doors": "4",
        "nb_visited_doors_last_30d": "4",
        "nb_surveys": "1",
        "nb_surveys_last_30d": "1"
    }
    """
