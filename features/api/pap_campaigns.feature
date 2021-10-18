@api
Feature:
  In order to see PAP campaigns
  As a non logged-in user
  I should be able to access API PAP campaigns

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData            |
      | LoadClientData              |
      | LoadScopeData               |
      | LoadPapCampaignData         |

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
    When I send a "GET" request to "/api/v3/pap_campaigns?scope=pap_national_manager"
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
