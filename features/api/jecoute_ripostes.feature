@api
@renaissance
Feature:
    In order to see ripostes
    I should be able to access API of ripostes

    Scenario Outline: As a non logged-in user I can not manage ripostes
        Given I send a "<method>" request to "<url>"
        Then the response status code should be 401

        Examples:
            | method | url                                                   |
            | POST   | /api/v3/ripostes                                      |
            | GET    | /api/v3/ripostes                                      |
            | GET    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4 |
            | PUT    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4 |
            | DELETE | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4 |

    Scenario Outline: As a logged-in user with no correct rights I can not manage ripostes
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web"
        When I send a "<method>" request to "<url>"
        Then the response status code should be 403

        Examples:
            | method | url                                                                  |
            | GET    | /api/v3/ripostes                                                     |
            | GET    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4                |
            | PUT    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4                |
            | DELETE | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4                |
            | PUT    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4/action/riposte |

    Scenario Outline: As a simple logged-in user on Jemengage mobile I can get and use ripostes
        Given I am logged with "simple-user@example.ch" via OAuth client "J'écoute" with scope "jemarche_app"
        When I send a "<method>" request to "<url>"
        Then the response status code should be 200

        Examples:
            | method | url                                                                  |
            | GET    | /api/v3/ripostes                                                     |
            | GET    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4                |
            | PUT    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4/action/riposte |

    Scenario Outline: As a logged-in user with correct rights I can get, edit or delete any riposte (not mine, disabled or old more that 24 hours)
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "<method>" request to "<url>?scope=national"
        Then the response status code should be <status>

        Examples:
            | method | url                                                                      | status |
            | GET    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4                    | 200    |
            | PUT    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4                    | 200    |
            | DELETE | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4                    | 204    |
            | PUT    | /api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4/action/riposte     | 200    |
            | GET    | /api/v3/ripostes/10ac465f-a2f9-44f1-9d80-8f2653a1b496                    | 200    |
            | PUT    | /api/v3/ripostes/10ac465f-a2f9-44f1-9d80-8f2653a1b496                    | 200    |
            | DELETE | /api/v3/ripostes/10ac465f-a2f9-44f1-9d80-8f2653a1b496                    | 204    |
            | PUT    | /api/v3/ripostes/10ac465f-a2f9-44f1-9d80-8f2653a1b496/action/detail_view | 200    |
            | GET    | /api/v3/ripostes/80b2eb70-38c3-425e-8c1d-a90e84e1a4b3                    | 200    |
            | PUT    | /api/v3/ripostes/80b2eb70-38c3-425e-8c1d-a90e84e1a4b3                    | 200    |
            | DELETE | /api/v3/ripostes/80b2eb70-38c3-425e-8c1d-a90e84e1a4b3                    | 204    |
            | PUT    | /api/v3/ripostes/80b2eb70-38c3-425e-8c1d-a90e84e1a4b3/action/source_view | 200    |

    Scenario: As a logged-in user I can retrieve all ripostes
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/ripostes?scope=national"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "title": "La plus récente riposte d'aujourd'hui avec un URL et notification",
                    "body": "Le texte de la plus récente riposte d'aujourd'hui avec un lien http://riposte.fr",
                    "source_url": "https://a-repondre.fr",
                    "enabled": true,
                    "with_notification": true,
                    "open_graph": {
                        "url": "https://a-repondre.fr",
                        "type": "Dummy OpenGraph type",
                        "image": "https://dummy-opengraph.com/image.jpg",
                        "title": "Dummy OpenGraph title",
                        "site_name": "Dummy OpenGraph site name",
                        "description": "Dummy OpenGraph description"
                    },
                    "uuid": "220bd36e-4ac4-488a-8473-8e99a71efba4",
                    "created_at": "@string@.isDateTime()",
                    "nb_ripostes": 1,
                    "nb_source_views": 1,
                    "nb_views": 1,
                    "nb_detail_views": 1,
                    "creator": "Admin"
                },
                {
                    "title": "La riposte d'aujourd'hui désactivée",
                    "body": "Le texte de la riposte d'aujourd'hui désactivée",
                    "source_url": "https://a-repondre.fr",
                    "enabled": false,
                    "with_notification": true,
                    "open_graph": {
                        "url": "https://a-repondre.fr",
                        "type": "Dummy OpenGraph type",
                        "image": "https://dummy-opengraph.com/image.jpg",
                        "title": "Dummy OpenGraph title",
                        "site_name": "Dummy OpenGraph site name",
                        "description": "Dummy OpenGraph description"
                    },
                    "uuid": "10ac465f-a2f9-44f1-9d80-8f2653a1b496",
                    "created_at": "@string@.isDateTime()",
                    "nb_ripostes": 0,
                    "nb_source_views": 0,
                    "nb_views": 0,
                    "nb_detail_views": 0,
                    "creator": "Admin"
                },
                {
                    "title": "La riposte avec URL et sans notification",
                    "body": "Le texte de la riposte avec URL et sans notification",
                    "source_url": "https://a-repondre.fr",
                    "with_notification": false,
                    "enabled": true,
                    "open_graph": {
                        "url": "https://a-repondre.fr",
                        "type": "Dummy OpenGraph type",
                        "image": "https://dummy-opengraph.com/image.jpg",
                        "title": "Dummy OpenGraph title",
                        "site_name": "Dummy OpenGraph site name",
                        "description": "Dummy OpenGraph description"
                    },
                    "uuid": "ff4a352e-9762-4da7-b9f3-a8bfdbce63c1",
                    "created_at": "@string@.isDateTime()",
                    "nb_ripostes": 1,
                    "nb_source_views": 0,
                    "nb_views": 1,
                    "nb_detail_views": 0,
                    "creator": "Député PARIS I"
                },
                {
                    "title": "La riposte d'avant-hier avec un URL et notification",
                    "body": "Le texte de la riposte d'avant-hier avec un lien http://riposte.fr",
                    "source_url": "https://a-repondre-avant-hier.fr",
                    "enabled": true,
                    "with_notification": true,
                    "open_graph": {
                        "url": "https://a-repondre-avant-hier.fr",
                        "type": "Dummy OpenGraph type",
                        "image": "https://dummy-opengraph.com/image.jpg",
                        "title": "Dummy OpenGraph title",
                        "site_name": "Dummy OpenGraph site name",
                        "description": "Dummy OpenGraph description"
                    },
                    "uuid": "80b2eb70-38c3-425e-8c1d-a90e84e1a4b3",
                    "created_at": "@string@.isDateTime()",
                    "nb_ripostes": 0,
                    "nb_source_views": 0,
                    "nb_views": 0,
                    "nb_detail_views": 0,
                    "creator": "Admin"
                }
            ]
            """

    Scenario: As a logged-in jemarche app user I can retrieve only active ripostes
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/ripostes"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "title": "La plus récente riposte d'aujourd'hui avec un URL et notification",
                    "body": "Le texte de la plus récente riposte d'aujourd'hui avec un lien http://riposte.fr",
                    "source_url": "https://a-repondre.fr",
                    "enabled": true,
                    "with_notification": true,
                    "open_graph": {
                        "url": "https://a-repondre.fr",
                        "type": "Dummy OpenGraph type",
                        "image": "https://dummy-opengraph.com/image.jpg",
                        "title": "Dummy OpenGraph title",
                        "site_name": "Dummy OpenGraph site name",
                        "description": "Dummy OpenGraph description"
                    },
                    "uuid": "220bd36e-4ac4-488a-8473-8e99a71efba4",
                    "created_at": "@string@.isDateTime()"
                },
                {
                    "title": "La riposte avec URL et sans notification",
                    "body": "Le texte de la riposte avec URL et sans notification",
                    "source_url": "https://a-repondre.fr",
                    "with_notification": false,
                    "enabled": true,
                    "open_graph": {
                        "url": "https://a-repondre.fr",
                        "type": "Dummy OpenGraph type",
                        "image": "https://dummy-opengraph.com/image.jpg",
                        "title": "Dummy OpenGraph title",
                        "site_name": "Dummy OpenGraph site name",
                        "description": "Dummy OpenGraph description"
                    },
                    "uuid": "ff4a352e-9762-4da7-b9f3-a8bfdbce63c1",
                    "created_at": "@string@.isDateTime()"
                }
            ]
            """

    Scenario: As a logged-in user I can get a riposte
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4?scope=national"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "title": "La plus récente riposte d'aujourd'hui avec un URL et notification",
                "body": "Le texte de la plus récente riposte d'aujourd'hui avec un lien http://riposte.fr",
                "source_url": "https://a-repondre.fr",
                "enabled": true,
                "with_notification": true,
                "open_graph": {
                    "url": "https://a-repondre.fr",
                    "type": "Dummy OpenGraph type",
                    "image": "https://dummy-opengraph.com/image.jpg",
                    "title": "Dummy OpenGraph title",
                    "site_name": "Dummy OpenGraph site name",
                    "description": "Dummy OpenGraph description"
                },
                "uuid": "220bd36e-4ac4-488a-8473-8e99a71efba4",
                "created_at": "@string@.isDateTime()",
                "nb_ripostes": 1,
                "nb_source_views": 1,
                "nb_views": 1,
                "nb_detail_views": 1,
                "creator": "Admin"
            }
            """

    Scenario: As a logged-in user with no correct rights I cannot create a riposte
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/ripostes?scope=national" with body:
            """
            {
                "title": "Une nouvelle riposte d'aujourd'hui",
                "body": "Le texte de la nouvelle riposte d'aujourd'hui ",
                "source_url": "aujourdhui.fr",
                "enabled": true,
                "with_notification": true
            }
            """
        Then the response status code should be 403

    Scenario: As a logged-in user I cannot create a riposte with no data
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/ripostes?scope=national" with body:
            """
            {}
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "title",
                        "message": "Cette valeur ne doit pas être vide."
                    },
                    {
                        "propertyPath": "body",
                        "message": "Cette valeur ne doit pas être vide."
                    },
                    {
                        "propertyPath": "source_url",
                        "message": "Cette valeur ne doit pas être vide."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I cannot create a riposte with invalid data
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/ripostes?scope=national" with body:
            """
            {
                "title": "Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui - Une nouvelle riposte d'aujourd'hui",
                "with_notification": true
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
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
                        "propertyPath": "source_url",
                        "message": "Cette valeur ne doit pas être vide."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can create a riposte
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        Then I should have 0 notification
        When I send a "POST" request to "/api/v3/ripostes?scope=national" with body:
            """
            {
                "title": "Une nouvelle riposte d'aujourd'hui",
                "body": "Le texte de la nouvelle riposte d'aujourd'hui",
                "source_url": "aujourdhui.fr",
                "with_notification": true
            }
            """
        Then the response status code should be 201
        And the JSON should be equal to:
            """
            {
                "title": "Une nouvelle riposte d'aujourd'hui",
                "body": "Le texte de la nouvelle riposte d'aujourd'hui",
                "source_url": "https://aujourdhui.fr",
                "with_notification": true,
                "enabled": true,
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "open_graph": {
                    "description": "Dummy OpenGraph description",
                    "image": "https://dummy-opengraph.com/image.jpg",
                    "site_name": "Dummy OpenGraph site name",
                    "title": "Dummy OpenGraph title",
                    "type": "Dummy OpenGraph type",
                    "url": "https://aujourdhui.fr"
                },
                "nb_ripostes": 0,
                "nb_source_views": 0,
                "nb_views": 0,
                "nb_detail_views": 0,
                "creator": "Député PARIS I"
            }
            """

    Scenario: As a logged-in user I can edit a riposte
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4?scope=national" with body:
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
                "created_at": "@string@.isDateTime()",
                "open_graph": {
                    "description": "Dummy OpenGraph description",
                    "image": "https://dummy-opengraph.com/image.jpg",
                    "site_name": "Dummy OpenGraph site name",
                    "title": "Dummy OpenGraph title",
                    "type": "Dummy OpenGraph type",
                    "url": "https://nouveau.fr"
                },
                "nb_ripostes": 1,
                "nb_source_views": 1,
                "nb_views": 1,
                "nb_detail_views": 1,
                "creator": "Admin"
            }
            """
        And I should have 0 notification

    Scenario: As a logged-in user I cannot increment number of invalid action on a riposte
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "PUT" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4/action/some_action"
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            {
                "code": "unknown_action",
                "message": "L'action n'est pas reconnue."
            }
            """

    Scenario: As a logged-in user I can increment number of some action on a riposte
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "PUT" request to "/api/v3/ripostes/220bd36e-4ac4-488a-8473-8e99a71efba4/action/source_view"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            "OK"
            """
