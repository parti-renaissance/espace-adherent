@api
@renaissance
Feature:
    In order to see event categories
    As a logged-in user
    I should be able to access API event categories

    Scenario: As a non logged-in user I get event categories with their group category
        When I send a "GET" request to "/api/event_categories"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Action ciblée",
                    "slug": "action-ciblee"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "Atelier",
                        "alert": null,
                        "slug": "atelier"
                    },
                    "alert": null,
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "ancrage local",
                    "slug": "ancrage-local"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Atelier du programme",
                    "slug": "atelier-du-programme"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Conférence-débat",
                    "slug": "conference-debat"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Convivialité",
                    "slug": "convivialite"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "Conférence",
                        "alert": null,
                        "slug": "conference"
                    },
                    "alert": null,
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Débat",
                    "slug": "debat"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "Évènements de campagne",
                        "alert": null,
                        "slug": "evenements-de-campagne"
                    },
                    "alert": null,
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Élections départementales",
                    "slug": "elections-departementales"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "Évènements de campagne",
                        "alert": null,
                        "slug": "evenements-de-campagne"
                    },
                    "alert": null,
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Élections régionales",
                    "slug": "elections-regionales"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Événement innovant",
                    "slug": "evenement-innovant"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Kiosque",
                    "slug": "kiosque"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Marche",
                    "slug": "marche"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Porte-à-porte",
                    "slug": "porte-a-porte"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "Atelier",
                        "alert": null,
                        "slug": "atelier"
                    },
                    "alert": null,
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "projets citoyens",
                    "slug": "projets-citoyens"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Réunion d'équipe",
                    "slug": "reunion-d-equipe"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Support party",
                    "slug": "support-party"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "événement",
                        "alert": null,
                        "slug": "evenement"
                    },
                    "alert": "@string@",
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Tractage",
                    "slug": "tractage"
                },
                {
                    "event_group_category": {
                        "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                        "name": "Atelier",
                        "alert": null,
                        "slug": "atelier"
                    },
                    "alert": null,
                    "description": "Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression.",
                    "name": "Un An",
                    "slug": "un-an"
                }
            ]
            """
