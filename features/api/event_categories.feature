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

  Scenario: As a non logged-in user I get event categories with their group category
    When I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/api/event_categories"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Action ciblée",
        "slug": "action-ciblee"
      },
      {
        "event_group_category": {
          "name": "Atelier",
          "slug": "atelier"
        },
        "name": "ancrage local",
        "slug": "ancrage-local"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Atelier du programme",
        "slug": "atelier-du-programme"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Conférence-débat",
        "slug": "conference-debat"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Convivialité",
        "slug": "convivialite"
      },
      {
        "event_group_category": {
          "name": "Conférence",
          "slug": "conference"
        },
        "name": "Débat",
        "slug": "debat"
      },
      {
        "event_group_category": {
          "name": "Évènements de campagne",
          "slug": "evenements-de-campagne"
        },
        "name": "Élections départementales",
        "slug": "elections-departementales"
      },
      {
        "event_group_category": {
          "name": "Évènements de campagne",
          "slug": "evenements-de-campagne"
        },
        "name": "Élections régionales",
        "slug": "elections-regionales"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Événement innovant",
        "slug": "evenement-innovant"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Kiosque",
        "slug": "kiosque"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Marche",
        "slug": "marche"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Porte-à-porte",
        "slug": "porte-a-porte"
      },
      {
        "event_group_category": {
          "name": "Atelier",
          "slug": "atelier"
        },
        "name": "projets citoyens",
        "slug": "projets-citoyens"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Réunion d'équipe",
        "slug": "reunion-dequipe"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Support party",
        "slug": "support-party"
      },
      {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Tractage",
        "slug": "tractage"
      },
      {
        "event_group_category": {
          "name": "Atelier",
          "slug": "atelier"
        },
        "name": "Un An",
        "slug": "un-an"
      }
    ]
    """
