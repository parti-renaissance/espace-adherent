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
      | LoadPhoningCampaignData         |
      | LoadPhoningCampaignHistoryData  |

  Scenario: As a logged-in user with no correct rights I cannot get my phoning campaigns
    Given I am logged with "benjyd@aol.com" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/scores"
    Then the response status code should be 403

  Scenario: As a logged-in user I can get my phoning campaigns
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaigns/scores"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
       {
          "title": "Campagne pour les hommes",
          "finish_at": "@string@.isDateTime()",
          "goal": 500,
          "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
       },
       {
          "title": "Campagne pour les femmes",
          "finish_at": "@string@.isDateTime()",
          "goal": 500,
          "uuid": "4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc"
       },
       {
          "title": "Campagne sans adhérents dispo à appeler",
          "finish_at": "@string@.isDateTime()",
          "goal": 100,
          "uuid": "b5e1b850-faec-4da7-8da6-d64b94494668"
       },
       {
          "title": "Campagne avec l'audience contenant tous les paramètres",
          "finish_at": "@string@.isDateTime()",
          "goal": 10,
          "uuid": "cc8f32ce-176c-42c8-a7e9-b854cc8fc61e"
       }
    ]
    """

  Scenario: As a non logged-in user I cannot get a phone number to call
    Given I send a "POST" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start"
    Then the response status code should be 401

  Scenario: As a logged-in user with no correct rights I cannot get a phone number to call
    Given I am logged with "carl999@example.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start"
    Then the response status code should be 403

  Scenario: As a logged-in user with correct rights I can get a phone number to call
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start"
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
     "adherent": {
       "age": 68,
       "city_name": "Paris 8e",
       "first_name": "Jacques",
       "gender": "male",
       "phone": {
         "country": "FR",
         "number": "01 87 26 42 36"
       },
       "postal_code": "75008",
       "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda"
     },
     "uuid": "@uuid@"
    }
    """

  Scenario: As a logged-in user with correct rights I cannot get a phone number to call if no available number
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/b5e1b850-faec-4da7-8da6-d64b94494668/start"
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {"message":"Aucun numéro à appeler disponible"}
    """

  Scenario: As a logged-in user with correct rights I cannot get a phone number to call if the campaign is finished
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I send a "POST" request to "/api/v3/phoning_campaigns/fdc99fb4-0492-4488-a53d-b7aa02888ffe/start"
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {"message":"Cette campagne est terminée"}
    """

  Scenario: As a non logged-in user I cannot get a phoning campaign history configuration
    Given I send a "GET" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config"
    Then the response status code should be 401

  Scenario: As a logged-in user, but not a caller of the phoning campaign history, I cannot get a phoning campaign history configuration
    Given I am logged with "benjyd@aol.com" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config"
    Then the response status code should be 403

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
                "code": "postal_code_checked",
                "label": "Code postal à jour ?",
                "type": "boolean"
            },
            {
                "code": "become_caller",
                "label": "Souhaiteriez-vous devenir appelant ?",
                "type": "boolean"
            },
            {
                "code": "call_more",
                "label": "Souhaitez-vous être rappelé plus souvent ?",
                "type": "boolean"
            }
        ]
    }
    """

  Scenario: As a non logged-in user I cannot change a phoning campaign history
    Given I send a "PUT" request to "/api/v3/phoning_campaigns/history/47bf09fb-db03-40c3-b951-6fe6bbe1f055"
    Then the response status code should be 401

  Scenario: As a logged-in user I cannot change not my phoning campaign history
    Given I am logged with "kiroule.p@blabla.tld" via OAuth client "JeMarche App"
    When I send a "PUT" request to "/api/v3/phoning_campaigns/history/47bf09fb-db03-40c3-b951-6fe6bbe1f055"
    Then the response status code should be 403

  Scenario: As a logged-in user I cannot change my phoning campaign history with wrong data
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/phoning_campaigns/history/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
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
       "detail":"status: Le status n'est pas valide.",
       "violations":[
          {
             "propertyPath":"status",
             "message":"Le status n'est pas valide."
          }
       ]
    }
    """

  Scenario: As a logged-in user I can change my phoning campaign history
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/phoning_campaigns/history/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
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
