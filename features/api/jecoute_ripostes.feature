@api
Feature:
  In order to see ripostes
  I should be able to access API of ripostes

  Background:
    Given the following fixtures are loaded:
      | LoadClientData          |
      | LoadAdherentData        |
      | LoadJecouteRiposteData  |

  Scenario Outline: As a non logged-in user I can not manage ripostes
    Given I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method  | url                                                     |
      | POST    | /api/v3/ripostes                                        |
      | GET     | /api/v3/ripostes                                        |
      | GET     | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4   |
      | PUT     | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4   |
      | DELETE  | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4   |

  Scenario Outline: As a logged-in user with no correct rights I can not manage ripostes
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Data-Corner"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 403
    Examples:
      | method  | url                                                     |
      | GET     | /api/v3/ripostes                                        |
      | GET     | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4   |
      | PUT     | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4   |
      | DELETE  | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4   |

  Scenario Outline: As a logged-in user I can get, edit or delete any riposte (not mine, disabled or old more that 24 hours)
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "<method>" request to "<url>"
    Then the response status code should be <status>
    Examples:
      | method  | url                                                   | status |
      | GET     | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4 | 200    |
      | PUT     | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4 | 200    |
      | DELETE  | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4 | 204    |
      | GET     | /api/v3/ripostes/80b2eb70-38c3-425e-8c1d-a90e84e1a4b3 | 200    |
      | PUT     | /api/v3/ripostes/80b2eb70-38c3-425e-8c1d-a90e84e1a4b3 | 200    |
      | DELETE  | /api/v3/ripostes/80b2eb70-38c3-425e-8c1d-a90e84e1a4b3 | 204    |
      | GET     | /api/v3/ripostes/5222890b-8cf7-45e3-909a-049f1ba5baa4 | 200    |
      | PUT     | /api/v3/ripostes/5222890b-8cf7-45e3-909a-049f1ba5baa4 | 200    |
      | DELETE  | /api/v3/ripostes/5222890b-8cf7-45e3-909a-049f1ba5baa4 | 204    |

  Scenario: As a logged-in user I can retrieve all ripostes
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/ripostes"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "title": "La plus récente riposte d'aujourd'hui avec un URL et notification",
        "body": "Le texte de la plus récente riposte d'aujourd'hui avec un lien http://riposte.fr",
        "source_url": "https://a-repondre.fr",
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
      },
      {
        "title": "La riposte d'aujourd'hui désactivé",
        "body": "Le texte de la riposte d'aujourd'hui désactivé",
        "source_url": null,
        "with_notification": true,
        "uuid": "80b2eb70-38c3-425e-8c1d-a90e84e1a4b3",
        "created_at": "@string@.isDateTime()"
      },
      {
        "title": "La riposte d'avant-hier avec un URL et notification",
        "body": "Le texte de la riposte d'avant-hier avec un lien http://riposte.fr",
        "source_url": "https://a-repondre-avant-hier.fr",
        "with_notification": true,
        "uuid": "5222890b-8cf7-45e3-909a-049f1ba5baa4",
        "created_at": "@string@.isDateTime()"
      }
    ]
    """

  Scenario: As a logged-in user I can retrieve only active ripostes
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/ripostes?active"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "title": "La plus récente riposte d'aujourd'hui avec un URL et notification",
        "body": "Le texte de la plus récente riposte d'aujourd'hui avec un lien http://riposte.fr",
        "source_url": "https://a-repondre.fr",
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
      "source_url": "https://a-repondre.fr",
      "with_notification": true,
      "enabled": true,
      "uuid": "220bd36e-4ac4-488a-8473-8e99a71efba4",
      "created_at": "@string@.isDateTime()"
    }
    """

  Scenario: As a logged-in user with no correct rights I cannot create a riposte
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/ripostes" with body:
    """
    {
      "title": "Une nouvelle riposte d'aujourd'hui",
      "body": "Le texte de la nouvelle riposte d'aujourd'hui ",
      "source_url": "aujourdhui.fr",
      "with_notification": true
    }
    """
    Then the response status code should be 403

  Scenario: As a logged-in user I cannot create a riposte with no data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/ripostes" with body:
    """
    {}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "type":"https://tools.ietf.org/html/rfc2616#section-10",
       "title":"An error occurred",
       "detail":"title: Cette valeur ne doit pas être vide.\nbody: Cette valeur ne doit pas être vide.",
       "violations":[
          {
             "propertyPath":"title",
             "message":"Cette valeur ne doit pas être vide."
          },
          {
             "propertyPath":"body",
             "message":"Cette valeur ne doit pas être vide."
          }
       ]
    }
    """

  Scenario: As a logged-in user I cannot create a riposte with invalid data
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/ripostes" with body:
    """
    {
      "title": "Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui",
      "with_notification": "true"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "title: Vous devez saisir au maximum 255 caractères.\nbody: Cette valeur ne doit pas être vide.\nwith_notification: Cette valeur doit être de type bool.",
      "violations": [
        {
          "propertyPath": "title",
          "message": "Vous devez saisir au maximum 255 caractères."
        },
        {
          "propertyPath": "body",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "with_notification",
          "message": "Cette valeur doit être de type bool."
        }
      ]
    }
    """

  Scenario: As a logged-in user I can create a riposte
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/ripostes" with body:
    """
    {
      "title": "Une nouvelle riposte d'aujourd'hui",
      "body": "Le texte de la nouvelle riposte d'aujourd'hui ",
      "source_url": "aujourdhui.fr",
      "with_notification": true
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
      "title": "Une nouvelle riposte d'aujourd'hui",
      "body": "Le texte de la nouvelle riposte d'aujourd'hui ",
      "source_url": "https://aujourdhui.fr",
      "with_notification": true,
      "enabled": true,
      "uuid": "@uuid@",
      "created_at": "@string@.isDateTime()"
    }
    """

  Scenario: As a logged-in user I can edit a riposte
    Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4" with body:
    """
    {
      "title": "Une nouvelle titre",
      "body": "Le nouveau texte",
      "source_url": "nouveau.fr",
      "with_notification": false,
      "enabled": false
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "title": "Une nouvelle titre",
      "body": "Le nouveau texte",
      "source_url": "https://nouveau.fr",
      "with_notification": false,
      "enabled": false,
      "uuid": "@uuid@",
      "created_at": "@string@.isDateTime()"
    }
    """
