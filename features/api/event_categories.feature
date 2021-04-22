@api
Feature:
  In order to see event categories
  As a logged-in user
  I should be able to access API event categories

  Background:
    Given the following fixtures are loaded:
      | LoadEventCategoryData   |
      | LoadAdherentData        |
      | LoadClientData          |

  Scenario: As a logged-in user I get event categories
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/v3/event_categories"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      {
        "name": "Action ciblée",
        "slug": "action-ciblee"
      },
      {
        "name": "ancrage local",
        "slug": "ancrage-local"
      },
      {
        "name": "Atelier du programme",
        "slug": "atelier-du-programme"
      },
      {
        "name": "Conférence-débat",
        "slug": "conference-debat"
      },
      {
        "name": "Convivialité",
        "slug": "convivialite"
      },
      {
        "name": "Débat",
        "slug": "debat"
      },
      {
        "name": "Élections départementales",
        "slug": "elections-departementales"
      },
      {
        "name": "Élections régionales",
        "slug": "elections-regionales"
      },
      {
        "name": "Événement innovant",
        "slug": "evenement-innovant"
      },
      {
        "name": "Kiosque",
        "slug": "kiosque"
      },
      {
        "name": "Marche",
        "slug": "marche"
      },
      {
        "name": "Porte-à-porte",
        "slug": "porte-a-porte"
      },
      {
        "name": "projets citoyens",
        "slug": "projets-citoyens"
      },
      {
        "name": "Réunion d'équipe",
        "slug": "reunion-dequipe"
      },
      {
        "name": "Support party",
        "slug": "support-party"
      },
      {
        "name": "Tractage",
        "slug": "tractage"
      },
      {
        "name": "Un An",
        "slug": "un-an"
      }
    ]
    """
