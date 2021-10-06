@api
Feature:
  In order to see phoning campaigns
  As a non logged-in user
  I should be able to access API phoning campaigns

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadClientData                  |
      | LoadTeamData                    |
      | LoadJecouteSurveyData           |
      | LoadPhoningCampaignData         |
      | LoadPhoningCampaignHistoryData  |
      | LoadCmsBlockData                |

  Scenario Outline: As a non logged-in user I cannot get and manage phoning campaigns
    Given I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method  | url                                                                                     |
      | POST    | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start                    |
      | GET     | /api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config   |
      | PUT     | /api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055                 |
      | GET     | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey                   |
      | GET     | /api/v3/phoning_campaigns/tutorial                                                      |

  Scenario Outline: As a logged-in user with no correct rights I cannot get regular phoning campaigns (only permanent)
    Given I am logged with "benjyd@aol.com" via OAuth client "JeMarche App"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                                                                   |
      | GET     | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/scores                 |
      | GET     | /api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config |
      | POST    | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start                  |

  Scenario: As a logged-in user I can get my phoning campaigns
    Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/scores"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "title": "Campagne pour les femmes",
        "brief": "### Campagne pour les femmes",
        "goal": 500,
        "finish_at": "@string@.isDateTime()",
        "uuid": "4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc",
        "nb_calls": 0,
        "nb_surveys": 0,
        "permanent": false,
        "scoreboard": [
          {
            "firstName": "Jacques",
            "nb_calls": "4",
            "nb_surveys": "3",
            "position": 1,
            "caller": false
          },
          {
            "firstName": "Pierre",
            "nb_calls": "1",
            "nb_surveys": "1",
            "position": 2,
            "caller": false
          },
          {
            "firstName": "Député",
            "nb_calls": "1",
            "nb_surveys": "1",
            "position": 3,
            "caller": false
          },
          {
            "firstName": "Lucie",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": 4,
            "caller": true
          }
        ]
      },
      {
        "title": "Campagne avec l'audience contenant tous les paramètres",
        "brief": "**Campagne** avec l'audience contenant tous les paramètres",
        "goal": 10,
        "finish_at": "@string@.isDateTime()",
        "uuid": "cc8f32ce-176c-42c8-a7e9-b854cc8fc61e",
        "nb_calls": 0,
        "nb_surveys": 0,
        "permanent": false,
        "scoreboard": [
          {
            "firstName": "Jacques",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": "@integer@",
            "caller": false
          },
          {
            "firstName": "Lucie",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": "@integer@",
            "caller": true
          },
          {
            "firstName": "Pierre",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": "@integer@",
            "caller": false
          },
          {
            "firstName": "Député",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": "@integer@",
            "caller": false
          }
        ]
      },
      {
        "brief": "# Campagne permanente !\n**Campagne** pour passer des appels à ses contacts",
        "finish_at": null,
        "goal": 42,
        "nb_calls": 0,
        "nb_surveys": 0,
        "permanent": true,
        "scoreboard": [],
        "title": "Campagne permanente",
        "uuid": "b48af58c-51e8-4f1b-a432-deace2969fda"
     }
    ]
    """

  Scenario: As a logged-in user I can get one of my phoning campaigns
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc/scores"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
      {
        "title": "Campagne pour les femmes",
        "brief": "### Campagne pour les femmes",
        "goal": 500,
        "finish_at": "@string@.isDateTime()",
        "uuid": "4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc",
        "nb_calls": 4,
        "nb_surveys": 3,
        "permanent": false,
        "scoreboard": [
          {
            "firstName": "Jacques",
            "nb_calls": "4",
            "nb_surveys": "3",
            "position": 1,
            "caller": true
          },
          {
            "firstName": "Pierre",
            "nb_calls": "1",
            "nb_surveys": "1",
            "position": 2,
            "caller": false
          },
          {
            "firstName": "Député",
            "nb_calls": "1",
            "nb_surveys": "1",
            "position": 3,
            "caller": false
          },
          {
            "firstName": "Lucie",
            "nb_calls": "0",
            "nb_surveys": "0",
            "position": 4,
            "caller": false
          }
        ]
      }
    """

  Scenario: As a logged-in user with correct rights I can get a phone number to call
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start"
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
     "adherent": {
       "info": "Député, @integer@ ans, habitant Paris 2e (75002). N’a encore jamais été appelé.",
       "gender": "male",
       "phone": {
         "country": "FR",
         "number": "01 87 65 67 81"
       },
       "uuid": "160cdf45-80c4-4663-aa21-0ae23091a381"
     },
     "uuid": "@uuid@"
    }
    """

  Scenario: As a logged-in user, I can start a call for my contact (permanent campaign)
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/b48af58c-51e8-4f1b-a432-deace2969fda/start"
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
     "adherent": null,
     "uuid": "@uuid@"
    }
    """

  Scenario: As a logged-in user with correct rights I cannot get a phone number to call if no available number
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/b5e1b850-faec-4da7-8da6-d64b94494668/start"
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "code": "no_available_number",
      "message": "Aucun numéro à appeler disponible"
    }
    """

  Scenario: As a logged-in user with correct rights I cannot get a phone number to call if the campaign is finished
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/fdc99fb4-0492-4488-a53d-b7aa02888ffe/start"
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "code": "finished_campaign",
      "message": "Cette campagne est terminée"
    }
    """

  Scenario: As a logged-in user, a caller of the phoning campaign history, I can get a phoning campaign history configuration
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "call_status": {
            "finished": [
                {
                    "code": "answered",
                    "label": "Il accepte de répondre aux questions"
                },
                {
                    "code": "to-unsubscribe",
                    "label": "Ne souhaite plus être rappelé"
                },
                {
                    "code": "to-unjoin",
                    "label": "Souhaite désadhérer"
                },
                {
                    "code": "not-respond",
                    "label": "N'a pa répondu au téléphone"
                },
                {
                    "code": "to-remind",
                    "label": "Souhaite être rappelé plus tard"
                },
                {
                    "code": "failed",
                    "label": "L'appel a échoué"
                }
            ],
            "interrupted": [
                {
                    "code": "interrupted-dont-remind",
                    "label": "Appel interrompu, ne pas rappeler"
                },
                {
                    "code": "interrupted",
                    "label": "Appel interrompu"
                }
            ]
        },
        "satisfaction_questions": [
            {
                "code": "need_sms_renewal",
                "label": "Souhaitez-vous vous réabonner à nos SMS ?",
                "type": "boolean"
            },
            {
                "code": "postal_code_checked",
                "label": "Habitez-vous toujours à Melun (77000) ?",
                "type": "boolean"
            },
            {
                "code": "profession",
                "label": "Quel est votre métier ?",
                "type": "text"
            },
            {
                "code": "engagement",
                "label": "Souhaitez-vous vous (re)engager sur le terrain ?",
                "type": "choice",
                "choices": {
                  "active": "Déjà actif",
                  "want_to_engage": "Souhaite se mobiliser",
                  "dont_want_to_engage": "Ne le souhaite pas"
                }
            },
            {
                "code": "note",
                "label": "Comment s'est passé cet appel ?",
                "type": "note",
                "values": [
                    1,
                    2,
                    3,
                    4,
                    5
                ]
            }
        ]
    }
    """

  Scenario: As a logged-in user I cannot change not my phoning campaign history
    Given I am logged with "kiroule.p@blabla.tld" via OAuth client "JeMarche App"
    When I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055"
    Then the response status code should be 403

  Scenario: As a logged-in user I cannot change my phoning campaign history with wrong data
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
    """
    {
        "status": "send"
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
       "type":"https://tools.ietf.org/html/rfc2616#section-10",
       "title":"An error occurred",
       "detail":"status: Le statut n'est pas valide.",
       "violations":[
          {
             "propertyPath":"status",
             "message":"Le statut n'est pas valide."
          }
       ]
    }
    """

  Scenario: As a logged-in user I can change only status of my phoning campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
    """
    {
        "status": "not-respond"
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "status": "not-respond",
        "uuid": "47bf09fb-db03-40c3-b951-6fe6bbe1f055"
    }
    """

  Scenario: As a logged-in user I can change my phoning campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
    """
    {
        "status": "completed",
        "postal_code_checked": true,
        "need_email_renewal": false,
        "need_sms_renewal": false,
        "engagement": "want_to_engage",
        "profession": "student",
        "type": "in-app",
        "note": 4
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "status": "completed",
        "uuid": "47bf09fb-db03-40c3-b951-6fe6bbe1f055"
    }
    """

  Scenario: As a logged-in user with no correct rights I cannot get a campaign survey
    Given I am logged with "benjyd@aol.com" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey"
    Then the response status code should be 403

  Scenario: As a logged-in user I can get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    And I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "id":1,
      "uuid":"@uuid@",
      "type": "national",
      "questions":[
        {
          "id":6,
          "type":"simple_field",
          "content":"Une première question du 1er questionnaire national ?",
          "choices":[]
        },
        {
          "id":7,
          "type":"multiple_choice",
          "content":"Une deuxième question du 1er questionnaire national ?",
          "choices":[
            {
              "id":5,
              "content":"Réponse nationale A"
            },
            {
              "id":6,
              "content":"Réponse nationale B"
            },
            {
              "id":7,
              "content":"Réponse nationale C"
            },
            {
              "id":8,
              "content":"Réponse nationale D"
            }
          ]
        }
      ],
      "name":"Questionnaire national numéro 1"
    }
    """

  Scenario: As a logged-in user I can get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    And I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "id":1,
      "uuid":"@uuid@",
      "type": "national",
      "questions":[
        {
          "id":6,
          "type":"simple_field",
          "content":"Une première question du 1er questionnaire national ?",
          "choices":[]
        },
        {
          "id":7,
          "type":"multiple_choice",
          "content":"Une deuxième question du 1er questionnaire national ?",
          "choices":[
            {
              "id":5,
              "content":"Réponse nationale A"
            },
            {
              "id":6,
              "content":"Réponse nationale B"
            },
            {
              "id":7,
              "content":"Réponse nationale C"
            },
            {
              "id":8,
              "content":"Réponse nationale D"
            }
          ]
        }
      ],
      "name":"Questionnaire national numéro 1"
    }
    """

  Scenario: As a logged-in user I can get a campaign survey
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    And I send a "GET" request to "/api/v3/phoning_campaigns/tutorial"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "content": "# Lorem ipsum\n\nLorem ipsum dolor sit amet, consectetur adipiscing elit."
    }
    """
