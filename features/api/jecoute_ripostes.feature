@api
Feature:
  In order to see ripostes
  I should be able to access API of ripostes

  Background:
    Given the following fixtures are loaded:
      | LoadClientData          |
      | LoadAdherentData        |
      | LoadJecouteRiposteData  |

  Scenario: As a logged-in user I can retrieve ripostes
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/ripostes"
    Then the response status code should be 200
    And the JSON should be equal to:
  """
  [
    {
      "title": "La plus récente riposte d'aujourd'hui avec un URL et notification",
      "body": "Le texte de la plus récente riposte d'aujourd'hui avec un lien http://riposte.fr",
      "source_url": "a-repondre.fr",
      "with_notification": true,
      "uuid": "220bd36e-4ac4-488a-8473-8e99a71efba4",
      "created_at": "@string@.isDateTime()"
    },
    {
      "title": "La riposte d'aujourd'hui sans URL",
      "body": "Le texte de la riposte d'aujourd'hui sans URL",
      "source_url": null,
      "with_notification": true,
      "uuid": "ff4a352e-9762-4da7-b9f3-a8bfdbce63c1",
      "created_at": "@string@.isDateTime()"
    },
    {
      "title": "La riposte sans URL et notification",
      "body": "Le texte de la riposte sans URL et notification",
      "source_url": null,
      "with_notification": false,
      "uuid": "10ac465f-a2f9-44f1-9d80-8f2653a1b496",
      "created_at": "@string@.isDateTime()"
    }
  ]
  """

  Scenario: As a logged-in user I can get a riposte
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4"
    Then the response status code should be 200
    And the JSON should be equal to:
  """
  {
    "title": "La plus récente riposte d'aujourd'hui avec un URL et notification",
    "body": "Le texte de la plus récente riposte d'aujourd'hui avec un lien http://riposte.fr",
    "source_url": "a-repondre.fr",
    "with_notification": true,
    "uuid": "220bd36e-4ac4-488a-8473-8e99a71efba4",
    "created_at": "@string@.isDateTime()"
  }
  """

  Scenario: As a logged-in user I cannot retrieve disabled riposte
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/ripostes/80b2eb70-38c3-425e-8c1d-a90e84e1a4b3"
    Then the response status code should be 404

  Scenario: As a logged-in user I cannot retrieve riposte created more than 24 hours
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/ripostes/5222890b-8cf7-45e3-909a-049f1ba5baa4"
    Then the response status code should be 404

  Scenario: As a non logged-in user I cannot retrieve ripostes
    Given I send a "GET" request to "/api/v3/ripostes"
    Then the response status code should be 401

  Scenario: As a non logged-in user I cannot get a riposte
    Given I send a "GET" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4"
    Then the response status code should be 401

  Scenario: As a logged-in user with no correct rights I cannot retrieve ripostes
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/ripostes"
    Then the response status code should be 403

  Scenario: As a logged-in user with no correct rights I cannot get a riposte
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4"
    Then the response status code should be 403
