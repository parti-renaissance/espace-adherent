@api
@renaissance
Feature:
    In order to see phoning campaigns
    As a non logged-in user
    I should be able to access API phoning campaigns

    Scenario Outline: As a non logged-in user I cannot get and manage phoning campaigns
        Given I send a "<method>" request to "<url>"
        Then the response status code should be 401

        Examples:
            | method | url                                                                                   |
            | POST   | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start                  |
            | GET    | /api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config |
            | PUT    | /api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055               |
            | GET    | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey                 |
            | GET    | /api/v3/phoning_campaigns/tutorial                                                    |

    Scenario Outline: As a logged-in user with no correct rights I cannot get regular phoning campaigns (only permanent)
        Given I am logged with "benjyd@aol.com" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "<method>" request to "<url>"
        Then the response status code should be 403

        Examples:
            | method | url                                                                                   |
            | GET    | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/scores                 |
            | GET    | /api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config |
            | POST   | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start                  |

    Scenario: As a user granted with national scope, I can get the list of national campaigns only
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaigns?scope=phoning_national_manager"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 7,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 4
                },
                "items": [
                    {
                        "title": "Campagne pour les hommes",
                        "goal": 500,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Première équipe de phoning",
                            "uuid": "3deeb1f5-819e-4629-85a1-eb75c916ce2f",
                            "visibility": "national",
                            "zone": null,
                            "members_count": 3
                        },
                        "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387",
                        "visibility": "national",
                        "zone": null,
                        "creator": "Admin",
                        "nb_calls": 12,
                        "nb_surveys": 6,
                        "participants_count": 0,
                        "nb_adherents_called": 8
                    },
                    {
                        "title": "Campagne pour les femmes",
                        "goal": 500,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Deuxième équipe de phoning",
                            "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                            "visibility": "national",
                            "zone": null,
                            "members_count": 4
                        },
                        "uuid": "4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc",
                        "visibility": "national",
                        "zone": null,
                        "creator": "Admin",
                        "nb_calls": 6,
                        "nb_surveys": 5,
                        "participants_count": 0,
                        "nb_adherents_called": 6
                    }
                ]
            }
            """

    Scenario: As a user granted with national scope, I can create a national campaign
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/phoning_campaigns?scope=phoning_national_manager" with body:
            """
            {
                "title": "Campagne Novembre 2021",
                "brief": "Cette Campagne est un test",
                "goal": 50,
                "finish_at": "+10 days",
                "team": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                "audience": {
                    "gender": "male",
                    "first_name": "john",
                    "last_name": "Doe",
                    "age_min": 30,
                    "age_max": 80,
                    "registered_since": "2020-11-08T10:25:20.677Z",
                    "is_certified": true,
                    "is_committee_member": false,
                    "has_sms_subscription": false,
                    "zones": ["e3efe563-906e-11eb-a875-0242ac150002"]
                },
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "title": "Campagne Novembre 2021",
                "brief": "Cette Campagne est un test",
                "goal": 50,
                "finish_at": "@string@.isDateTime()",
                "team": {
                    "name": "Deuxième équipe de phoning",
                    "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "members_count": 4,
                    "visibility": "national",
                    "zone": null
                },
                "audience": {
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "zones": [
                        {
                            "uuid": "e3efe563-906e-11eb-a875-0242ac150002",
                            "code": "75",
                            "name": "Paris",
                            "created_at": "@string@.isDateTime()",
                            "updated_at": "@string@.isDateTime()"
                        }
                    ],
                    "first_name": "john",
                    "last_name": "Doe",
                    "gender": "male",
                    "age_min": 30,
                    "age_max": 80,
                    "registered_since": "@string@.isDateTime()",
                    "registered_until": null,
                    "is_committee_member": false,
                    "is_certified": true,
                    "has_email_subscription": null,
                    "has_sms_subscription": true
                },
                "survey": {
                    "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Questionnaire national numéro 1"
                },
                "permanent": false,
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "creator": "Député P.",
                "nb_calls": 0,
                "nb_surveys": 0,
                "participants_count": 0,
                "nb_adherents_called": 0,
                "visibility": "national",
                "zone": null
            }
            """

    Scenario: As a user granted with national scope, I can update a national campaign
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/phoning_campaigns/b5e1b850-faec-4da7-8da6-d64b94494668?scope=phoning_national_manager" with body:
            """
            {
                "title": "**NOUVEAU** Campagne sans adhérent dispo à appeler"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "title": "**NOUVEAU** Campagne sans adhérent dispo à appeler",
                "brief": null,
                "goal": 100,
                "finish_at": "@string@.isDateTime()",
                "team": {
                    "name": "Première équipe de phoning",
                    "uuid": "3deeb1f5-819e-4629-85a1-eb75c916ce2f",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "visibility": "national",
                    "zone": null,
                    "members_count": 3
                },
                "audience": {
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "zones": [],
                    "first_name": null,
                    "last_name": null,
                    "gender": "other",
                    "age_min": null,
                    "age_max": null,
                    "registered_since": null,
                    "registered_until": null,
                    "is_committee_member": null,
                    "is_certified": null,
                    "has_email_subscription": null,
                    "has_sms_subscription": true
                },
                "survey": {
                    "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Questionnaire national numéro 1"
                },
                "permanent": false,
                "uuid": "b5e1b850-faec-4da7-8da6-d64b94494668",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "visibility": "national",
                "zone": null,
                "creator": "Admin",
                "nb_calls": 0,
                "nb_surveys": 0,
                "participants_count": 0,
                "nb_adherents_called": 0
            }
            """

    Scenario: As a user granted with national scope, I can not create a local campaign
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/phoning_campaigns?scope=phoning_national_manager" with body:
            """
            {
                "title": "Campagne Novembre 2021",
                "brief": "Cette Campagne est un test",
                "goal": 50,
                "finish_at": "+10 days",
                "team": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                "audience": {
                    "gender": "male",
                    "first_name": "john",
                    "last_name": "Doe",
                    "age_min": 30,
                    "age_max": 80,
                    "registered_since": "2020-11-08T10:25:20.677Z",
                    "is_certified": true,
                    "is_committee_member": false,
                    "has_sms_subscription": false,
                    "zones": ["e3efe563-906e-11eb-a875-0242ac150002"]
                },
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                "zone": "e3f21338-906e-11eb-a875-0242ac150002"
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
                        "message": "Un rôle national ne peut pas définir de zone.",
                        "propertyPath": "zone"
                    }
                ]
            }
            """

    Scenario: As a user granted with national scope, I can not update a local campaign
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/phoning_campaigns/2c0f981b-4e2a-448a-a0c2-aebca3b3eb1e?scope=phoning_national_manager" with body:
            """
            {
                "title": "**NOUVEAU** Campagne locale du département 92"
            }
            """
        Then the response status code should be 403

    Scenario Outline: As a user granted with (delegated) local scope, I can get the list of local campaigns in the zones I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        And I send a "GET" request to "/api/v3/phoning_campaigns?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 2,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "title": "Campagne locale du département 92",
                        "goal": 10,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Première équipe de phoning",
                            "uuid": "3deeb1f5-819e-4629-85a1-eb75c916ce2f",
                            "visibility": "national",
                            "zone": null,
                            "members_count": 3
                        },
                        "uuid": "2c0f981b-4e2a-448a-a0c2-aebca3b3eb1e",
                        "visibility": "local",
                        "zone": {
                            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        },
                        "creator": "Admin",
                        "nb_calls": 0,
                        "nb_surveys": 0,
                        "participants_count": 0,
                        "nb_adherents_called": 0
                    },
                    {
                        "title": "Campagne locale de la ville de Lille (59350)",
                        "goal": 10,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Première équipe de phoning",
                            "uuid": "3deeb1f5-819e-4629-85a1-eb75c916ce2f",
                            "visibility": "national",
                            "zone": null,
                            "members_count": 3
                        },
                        "uuid": "d687cd2a-0870-49de-ba12-468202f70099",
                        "visibility": "local",
                        "zone": {
                            "uuid": "e3f21338-906e-11eb-a875-0242ac150002",
                            "code": "59350",
                            "name": "Lille"
                        },
                        "creator": "Admin",
                        "nb_calls": 0,
                        "nb_surveys": 0,
                        "participants_count": 0,
                        "nb_adherents_called": 0
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get a local campaign in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        And I send a "GET" request to "/api/v3/phoning_campaigns/d687cd2a-0870-49de-ba12-468202f70099?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "title": "Campagne locale de la ville de Lille (59350)",
                "brief": null,
                "goal": 10,
                "finish_at": "@string@.isDateTime()",
                "team": {
                    "name": "Première équipe de phoning",
                    "uuid": "3deeb1f5-819e-4629-85a1-eb75c916ce2f",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "visibility": "national",
                    "zone": null,
                    "members_count": 3
                },
                "audience": {
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "zones": [],
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "age_min": null,
                    "age_max": null,
                    "registered_since": null,
                    "registered_until": null,
                    "is_committee_member": null,
                    "is_certified": null,
                    "has_email_subscription": null,
                    "has_sms_subscription": null
                },
                "survey": {
                    "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Questionnaire national numéro 1"
                },
                "permanent": false,
                "uuid": "d687cd2a-0870-49de-ba12-468202f70099",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "visibility": "local",
                "zone": {
                    "uuid": "e3f21338-906e-11eb-a875-0242ac150002",
                    "code": "59350",
                    "name": "Lille",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()"
                },
                "creator": "Admin",
                "nb_calls": 0,
                "nb_surveys": 0,
                "participants_count": 0,
                "nb_adherents_called": 0,
                "nb_un_join": 0,
                "nb_un_subscribe": 0,
                "to_remind": 0,
                "not_respond": 0,
                "nb_failed": 0,
                "average_calling_time": 0
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can create a local campaign in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/phoning_campaigns?scope=<scope>" with body:
            """
            {
                "title": "Campagne Novembre 2021",
                "brief": "Cette Campagne est un test",
                "goal": 50,
                "finish_at": "+10 days",
                "team": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                "audience": {
                    "gender": "male",
                    "first_name": "john",
                    "last_name": "Doe",
                    "age_min": 30,
                    "age_max": 80,
                    "registered_since": "2020-11-08T10:25:20.677Z",
                    "is_certified": true,
                    "is_committee_member": false,
                    "has_sms_subscription": false,
                    "zones": ["e3efe563-906e-11eb-a875-0242ac150002"]
                },
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                "zone": "e3f21338-906e-11eb-a875-0242ac150002"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "audience": {
                    "age_max": 80,
                    "age_min": 30,
                    "created_at": "@string@.isDateTime()",
                    "first_name": "john",
                    "gender": "male",
                    "has_email_subscription": null,
                    "has_sms_subscription": true,
                    "is_certified": true,
                    "is_committee_member": false,
                    "last_name": "Doe",
                    "registered_since": "2020-11-08T00:00:00+01:00",
                    "registered_until": null,
                    "updated_at": "@string@.isDateTime()",
                    "uuid": "@uuid@",
                    "zones": [
                        {
                            "code": "75",
                            "created_at": "@string@.isDateTime()",
                            "name": "Paris",
                            "updated_at": "@string@.isDateTime()",
                            "uuid": "e3efe563-906e-11eb-a875-0242ac150002"
                        }
                    ]
                },
                "brief": "Cette Campagne est un test",
                "created_at": "@string@.isDateTime()",
                "creator": "Referent R.",
                "finish_at": "@string@.isDateTime()",
                "goal": 50,
                "nb_calls": 0,
                "nb_surveys": 0,
                "participants_count": 0,
                "nb_adherents_called": 0,
                "permanent": false,
                "survey": {
                    "created_at": "@string@.isDateTime()",
                    "name": "Questionnaire national numéro 1",
                    "updated_at": "@string@.isDateTime()",
                    "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
                },
                "team": {
                    "created_at": "@string@.isDateTime()",
                    "members_count": 4,
                    "name": "Deuxième équipe de phoning",
                    "updated_at": "@string@.isDateTime()",
                    "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                    "visibility": "national",
                    "zone": null
                },
                "title": "Campagne Novembre 2021",
                "updated_at": "@string@.isDateTime()",
                "uuid": "@uuid@",
                "visibility": "local",
                "zone": {
                    "code": "59350",
                    "created_at": "@string@.isDateTime()",
                    "name": "Lille",
                    "updated_at": "@string@.isDateTime()",
                    "uuid": "e3f21338-906e-11eb-a875-0242ac150002"
                }
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can update a local campaign in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/phoning_campaigns/2c0f981b-4e2a-448a-a0c2-aebca3b3eb1e?scope=<scope>" with body:
            """
            {
                "title": "**NOUVEAU** Campagne locale du département 92"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "audience": {
                    "age_max": null,
                    "age_min": null,
                    "created_at": "@string@.isDateTime()",
                    "first_name": null,
                    "gender": null,
                    "has_email_subscription": null,
                    "has_sms_subscription": true,
                    "is_certified": null,
                    "is_committee_member": null,
                    "last_name": null,
                    "registered_since": null,
                    "registered_until": null,
                    "updated_at": "@string@.isDateTime()",
                    "uuid": "@uuid@",
                    "zones": []
                },
                "brief": null,
                "created_at": "@string@.isDateTime()",
                "creator": "Admin",
                "finish_at": "@string@.isDateTime()",
                "goal": 10,
                "nb_calls": 0,
                "nb_surveys": 0,
                "participants_count": 1,
                "nb_adherents_called": 0,
                "permanent": false,
                "survey": {
                    "created_at": "@string@.isDateTime()",
                    "name": "Questionnaire national numéro 1",
                    "updated_at": "@string@.isDateTime()",
                    "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
                },
                "team": {
                    "created_at": "@string@.isDateTime()",
                    "members_count": 3,
                    "name": "Première équipe de phoning",
                    "updated_at": "@string@.isDateTime()",
                    "uuid": "3deeb1f5-819e-4629-85a1-eb75c916ce2f",
                    "visibility": "national",
                    "zone": null
                },
                "title": "**NOUVEAU** Campagne locale du département 92",
                "updated_at": "@string@.isDateTime()",
                "uuid": "2c0f981b-4e2a-448a-a0c2-aebca3b3eb1e",
                "visibility": "local",
                "zone": {
                    "code": "92",
                    "created_at": "@string@.isDateTime()",
                    "name": "Hauts-de-Seine",
                    "updated_at": "2021-04-22T12:08:51+02:00",
                    "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002"
                }
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can not create a local campaign in a zone I am not manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/phoning_campaigns?scope=<scope>" with body:
            """
            {
                "title": "Campagne Novembre 2021",
                "brief": "Cette Campagne est un test",
                "goal": 50,
                "finish_at": "+10 days",
                "team": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                "audience": {
                    "gender": "male",
                    "first_name": "john",
                    "last_name": "Doe",
                    "age_min": 30,
                    "age_max": 80,
                    "registered_since": "2020-11-08T10:25:20.677Z",
                    "is_certified": true,
                    "is_committee_member": false,
                    "has_sms_subscription": false,
                    "zones": ["e3efe563-906e-11eb-a875-0242ac150002"]
                },
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                "zone": "e3f1a8e8-906e-11eb-a875-0242ac150002"
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
                        "propertyPath": "zone",
                        "message": "La zone spécifiée n'est pas gérée par votre rôle."
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can not update a local campaign in a zone I am not manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/phoning_campaigns/f909c7b5-aafd-4785-8b09-edebbf5814ee?scope=<scope>" with body:
            """
            {
                "title": "**NOUVEAU** Campagne locale de la ville de Nice (06088)"
            }
            """
        Then the response status code should be 403

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can not create a national campaign
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/phoning_campaigns?scope=<scope>" with body:
            """
            {
                "title": "Campagne Novembre 2021",
                "brief": "Cette Campagne est un test",
                "goal": 50,
                "finish_at": "+10 days",
                "team": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                "audience": {
                    "gender": "male",
                    "first_name": "john",
                    "last_name": "Doe",
                    "age_min": 30,
                    "age_max": 80,
                    "registered_since": "2020-11-08T10:25:20.677Z",
                    "is_certified": true,
                    "is_committee_member": false,
                    "has_sms_subscription": false,
                    "zones": ["e3efe563-906e-11eb-a875-0242ac150002"]
                },
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
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
                        "propertyPath": "zone",
                        "message": "Veuillez spécifier une zone."
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can not update a national campaign
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/phoning_campaigns/b5e1b850-faec-4da7-8da6-d64b94494668?scope=<scope>" with body:
            """
            {
                "title": "**NOUVEAU** Campagne sans adhérent dispo à appeler"
            }
            """
        Then the response status code should be 403

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As an anonymous user, I can not get the list of campaigns
        Given I send a "GET" request to "/api/v3/phoning_campaigns?scope=president_departmental_assembly"
        Then the response status code should be 401

    Scenario: As an anonymous user, I can not create a campaign
        When I send a "POST" request to "/api/v3/phoning_campaigns?scope=phoning_national_manager" with body:
            """
            {
                "title": "Campagne Novembre 2021",
                "brief": "Cette Campagne est un test",
                "goal": 50,
                "finish_at": "+10 days",
                "team": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                "audience": {
                    "gender": "male",
                    "first_name": "john",
                    "last_name": "Doe",
                    "age_min": 30,
                    "age_max": 80,
                    "registered_since": "2020-11-08T10:25:20.677Z",
                    "is_certified": true,
                    "is_committee_member": false,
                    "has_sms_subscription": false,
                    "zones": ["e3efe563-906e-11eb-a875-0242ac150002"]
                },
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
            }
            """
        Then the response status code should be 401

    Scenario: As an anonymous user, I can not update a campaign
        When I send a "PUT" request to "/api/v3/phoning_campaigns/b5e1b850-faec-4da7-8da6-d64b94494668?scope=phoning_national_manager" with body:
            """
            {
                "title": "**NOUVEAU** Campagne sans adhérent dispo à appeler"
            }
            """
        Then the response status code should be 401

    Scenario Outline: As a logged-in user with no correct rights I cannot get phoning campaigns
        Given I am logged with "benjyd@aol.com" via OAuth client "JeMengage Web"
        When I send a "<method>" request to "<url>"
        Then the response status code should be 403

        Examples:
            | method | url                                                                                                   |
            | GET    | /api/v3/phoning_campaign_histories?scope=phoning_national_manager                                     |
            | GET    | /api/v3/phoning_campaigns?scope=phoning_national_manager                                              |
            | GET    | /api/v3/phoning_campaigns/kpi?scope=phoning_national_manager                                          |
            | GET    | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387?scope=phoning_national_manager         |
            | GET    | /api/v3/phoning_campaigns/9ca189b7-7635-4c3a-880b-6ce5cd10e8bc/replies?scope=phoning_national_manager |
            | GET    | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/callers?scope=phoning_national_manager |
            | PUT    | /api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387?scope=phoning_national_manager         |

    Scenario: As a logged-in user I can get my phoning campaigns
        Given I am logged with "luciole1989@spambox.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/phoning_campaigns/scores"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
              {
                "title": "Campagne sur l'horizon 2030",
                "brief": "### Décidons aujourd'hui de la France de demain",
                "goal": 500,
                "finish_at": "@string@.isDateTime()",
                "uuid": "9ca189b7-7635-4c3a-880b-6ce5cd10e8bc",
                "nb_calls": 4,
                "nb_surveys": 3,
                "permanent": false,
                "scoreboard": [
                  {
                    "firstName": "Lucie",
                    "nb_calls": 4,
                    "nb_surveys": 3,
                    "position": 1,
                    "caller": true
                  },
                  {
                    "firstName": "Jacques",
                    "nb_calls": 0,
                    "nb_surveys": 0,
                    "position": 2,
                    "caller": false
                  },
                  {
                    "firstName": "Pierre",
                    "nb_calls": 0,
                    "nb_surveys": 0,
                    "position": 3,
                    "caller": false
                  },
                  {
                    "firstName": "Député",
                    "nb_calls": 0,
                    "nb_surveys": 0,
                    "position": 4,
                    "caller": false
                  }
                ]
              },
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
                    "nb_calls": 4,
                    "nb_surveys": 3,
                    "position": 1,
                    "caller": false
                  },
                  {
                    "firstName": "Député",
                    "nb_calls": 1,
                    "nb_surveys": 1,
                    "position": 2,
                    "caller": false
                  },
                  {
                    "firstName": "Pierre",
                    "nb_calls": 1,
                    "nb_surveys": 1,
                    "position": 3,
                    "caller": false
                  },
                  {
                    "firstName": "Lucie",
                    "nb_calls": 0,
                    "nb_surveys": 0,
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
                    "nb_calls": 0,
                    "nb_surveys": 0,
                    "position": @integer@,
                    "caller": false
                  },
                  {
                    "firstName": "Lucie",
                    "nb_calls": 0,
                    "nb_surveys": 0,
                    "position": @integer@,
                    "caller": true
                  },
                  {
                    "firstName": "Pierre",
                    "nb_calls": 0,
                    "nb_surveys": 0,
                    "position": @integer@,
                    "caller": false
                  },
                  {
                    "firstName": "Député",
                    "nb_calls": 0,
                    "nb_surveys": 0,
                    "position": @integer@,
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
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
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
                        "nb_calls": 4,
                        "nb_surveys": 3,
                        "position": 1,
                        "caller": true
                    },
                    {
                        "firstName": "Député",
                        "nb_calls": 1,
                        "nb_surveys": 1,
                        "position": 2,
                        "caller": false
                    },
                    {
                        "firstName": "Pierre",
                        "nb_calls": 1,
                        "nb_surveys": 1,
                        "position": 3,
                        "caller": false
                    },
                    {
                        "firstName": "Lucie",
                        "nb_calls": 0,
                        "nb_surveys": 0,
                        "position": 4,
                        "caller": false
                    }
                ]
            }
            """

    Scenario: As a logged-in user with correct rights I can get a phone number to call
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/start"
        Then the response status code should be 201
        And the JSON should be equal to:
            """
            {
                "adherent": {
                    "info": "@string@. N’a encore jamais été appelé.",
                    "gender": "male",
                    "phone": {
                        "country": "FR",
                        "number": "@string@"
                    },
                    "uuid": "@uuid@"
                },
                "uuid": "@uuid@"
            }
            """

    Scenario: As a logged-in user, I can start a call for my contact (permanent campaign)
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
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
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
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
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
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
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055/survey-config"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "call_status": {
                    "finished": [
                        {
                            "code": "answered",
                            "label": "Accepte de répondre aux questions"
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
                            "label": "N'a pas répondu au téléphone"
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
                        "values": [1, 2, 3, 4, 5]
                    }
                ]
            }
            """

    Scenario: As a logged-in user I cannot change not my phoning campaign history
        Given I am logged with "kiroule.p@blabla.tld" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055"
        Then the response status code should be 403

    Scenario: As a logged-in user I cannot change my phoning campaign history with wrong data
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
            """
            {
                "status": "send"
            }
            """
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "status",
                        "message": "Le statut n'est pas valide."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can change only status of my phoning campaign history
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
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
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "PUT" request to "/api/v3/phoning_campaign_histories/47bf09fb-db03-40c3-b951-6fe6bbe1f055" with body:
            """
            {
                "status": "completed",
                "postal_code_checked": true,
                "need_email_renewal": true,
                "need_sms_renewal": false,
                "engagement": "want_to_engage",
                "profession": "student",
                "type": "in-app",
                "note": 4
            }
            """
        Then the response status code should be 200
        And I should have 1 email "PhoningCampaignAdherentActionSummaryMessage" for "adherent-male-39@en-marche-dev.fr" with payload:
            """
            {
                "template_name": "phoning-campaign-adherent-action-summary",
                "template_content": [],
                "message": {
                    "subject": "[Désignations] Une candidature a été retirée",
                    "from_email": "contact@parti-renaissance.fr",
                    "from_name": "Renaissance",
                    "html": null,
                    "merge_vars": [
                        {
                            "rcpt": "adherent-male-39@en-marche-dev.fr",
                            "vars": [
                                {
                                    "content": "Adherent 39",
                                    "name": "first_name"
                                },
                                {
                                    "content": "http://test.renaissance.code/espace-adherent/preferences-des-emails?autorun=1",
                                    "name": "email_subscribe_url"
                                },
                                {
                                    "content": null,
                                    "name": "sms_preference_url"
                                },
                                {
                                    "content": null,
                                    "name": "edit_profil_url"
                                }
                            ]
                        }
                    ],
                    "subject": "Suite à notre appel",
                    "to": [
                        {
                            "email": "adherent-male-39@en-marche-dev.fr",
                            "name": "Adherent 39 Fa39ke",
                            "type": "to"
                        }
                    ]
                }
            }
            """
        And the JSON should be equal to:
            """
            {
                "status": "completed",
                "uuid": "47bf09fb-db03-40c3-b951-6fe6bbe1f055"
            }
            """

    Scenario: As a logged-in user with no correct rights I cannot get a campaign survey
        Given I am logged with "benjyd@aol.com" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey"
        Then the response status code should be 403

    Scenario: As a logged-in user I can get a campaign survey
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/survey"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
              "id": @integer@,
              "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
              "type": "national",
              "questions":[
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
                      "content": "Réponse nationale A"
                    },
                    {
                      "id": @integer@,
                      "content": "Réponse nationale B"
                    },
                    {
                      "id": @integer@,
                      "content": "Réponse nationale C"
                    },
                    {
                      "id": @integer@,
                      "content": "Réponse nationale D"
                    }
                  ]
                }
              ],
              "name": "Questionnaire national numéro 1"
            }
            """

    Scenario: As a logged-in user I can get the phoning campaigns survey tutorial
        Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "GET" request to "/api/v3/phoning_campaigns/tutorial"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "content": "# Lorem ipsum\n\nLorem ipsum dolor sit amet, consectetur adipiscing elit."
            }
            """

    Scenario: As a phoning national manager I can get the list of phoning campaigns
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaigns?scope=phoning_national_manager&page_size=10"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 7,
                    "items_per_page": 10,
                    "count": 7,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "title": "Campagne pour les hommes",
                        "goal": 500,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Première équipe de phoning",
                            "uuid": "@uuid@",
                            "members_count": 3,
                            "visibility": "national",
                            "zone": null
                        },
                        "uuid": "@uuid@",
                        "creator": "Admin",
                        "nb_calls": 12,
                        "nb_surveys": 6,
                        "participants_count": 0,
                        "nb_adherents_called": 8,
                        "visibility": "national",
                        "zone": null
                    },
                    {
                        "title": "Campagne pour les femmes",
                        "goal": 500,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Deuxième équipe de phoning",
                            "uuid": "@uuid@",
                            "members_count": 4,
                            "visibility": "national",
                            "zone": null
                        },
                        "uuid": "@uuid@",
                        "creator": "Admin",
                        "nb_calls": 6,
                        "nb_surveys": 5,
                        "participants_count": 0,
                        "nb_adherents_called": 6,
                        "visibility": "national",
                        "zone": null
                    },
                    {
                        "title": "Campagne sur l'horizon 2030",
                        "goal": 500,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Deuxième équipe de phoning",
                            "uuid": "@uuid@",
                            "members_count": 4,
                            "visibility": "national",
                            "zone": null
                        },
                        "uuid": "@uuid@",
                        "creator": "Admin",
                        "nb_calls": 4,
                        "nb_surveys": 3,
                        "participants_count": 0,
                        "nb_adherents_called": 4,
                        "visibility": "national",
                        "zone": null
                    },
                    {
                        "title": "Campagne terminée",
                        "goal": 100,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Première équipe de phoning",
                            "uuid": "@uuid@",
                            "members_count": 3,
                            "visibility": "national",
                            "zone": null
                        },
                        "uuid": "@uuid@",
                        "creator": "Admin",
                        "nb_calls": 0,
                        "nb_surveys": 0,
                        "participants_count": 0,
                        "nb_adherents_called": 0,
                        "visibility": "national",
                        "zone": null
                    },
                    {
                        "title": "Campagne sans adhérent dispo à appeler",
                        "goal": 100,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Première équipe de phoning",
                            "uuid": "@uuid@",
                            "members_count": 3,
                            "visibility": "national",
                            "zone": null
                        },
                        "uuid": "@uuid@",
                        "creator": "Admin",
                        "nb_calls": 0,
                        "nb_surveys": 0,
                        "participants_count": 0,
                        "nb_adherents_called": 0,
                        "visibility": "national",
                        "zone": null
                    },
                    {
                        "title": "Campagne avec l'audience contenant tous les paramètres",
                        "goal": 10,
                        "finish_at": "@string@.isDateTime()",
                        "team": {
                            "name": "Deuxième équipe de phoning",
                            "uuid": "@uuid@",
                            "members_count": 4,
                            "visibility": "national",
                            "zone": null
                        },
                        "uuid": "@uuid@",
                        "creator": "Admin",
                        "nb_calls": 0,
                        "nb_surveys": 0,
                        "participants_count": 0,
                        "nb_adherents_called": 0,
                        "visibility": "national",
                        "zone": null
                    },
                    {
                        "title": "Campagne permanente",
                        "goal": 42,
                        "finish_at": null,
                        "team": null,
                        "uuid": "@uuid@",
                        "creator": "Admin",
                        "nb_calls": 1,
                        "nb_surveys": 0,
                        "participants_count": 0,
                        "nb_adherents_called": 0,
                        "visibility": "national",
                        "zone": null
                    }
                ]
            }
            """

    Scenario: As a phoning national manager I can get one phone campaign
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387?scope=phoning_national_manager&page_size=10"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "title": "Campagne pour les hommes",
                "brief": "**Campagne** pour les hommes",
                "goal": 500,
                "finish_at": "@string@.isDateTime()",
                "team": {
                    "name": "Première équipe de phoning",
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "members_count": 3,
                    "visibility": "national",
                    "zone": null
                },
                "audience": {
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "zones": [
                        {
                            "uuid": "@uuid@",
                            "code": "75056",
                            "name": "Paris",
                            "created_at": "@string@.isDateTime()",
                            "updated_at": "@string@.isDateTime()"
                        }
                    ],
                    "first_name": null,
                    "last_name": null,
                    "gender": "male",
                    "age_min": null,
                    "age_max": null,
                    "registered_since": null,
                    "registered_until": null,
                    "is_committee_member": null,
                    "is_certified": null,
                    "has_email_subscription": null,
                    "has_sms_subscription": null
                },
                "survey": {
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Questionnaire national numéro 1"
                },
                "permanent": false,
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "creator": "Admin",
                "nb_calls": 12,
                "nb_surveys": 6,
                "participants_count": 0,
                "nb_adherents_called": 8,
                "nb_un_join": 1,
                "nb_un_subscribe": 1,
                "to_remind": 1,
                "not_respond": 2,
                "nb_failed": 1,
                "average_calling_time": 0,
                "visibility": "national",
                "zone": null
            }
            """

    Scenario: As a phoning national manager I can create a new phoning campaign
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/phoning_campaigns?scope=phoning_national_manager" with body:
            """
            {
                "title": "Campagne Novembre 2021",
                "brief": "cette Campagne est un test",
                "goal": 50,
                "finish_at": "+10 days",
                "team": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                "audience": {
                    "gender": "male",
                    "firstName": "john",
                    "lastName": "Doe",
                    "ageMin": 30,
                    "ageMax": 80,
                    "registeredSince": "2020-11-08T10:25:20.677Z",
                    "isCertified": true,
                    "isCommitteeMember": false,
                    "hasSmsSubscription": false,
                    "zones": ["e3efe563-906e-11eb-a875-0242ac150002"]
                },
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
            }
            """
        Then the response status code should be 201
        And the JSON should be equal to:
            """
            {
                "title": "Campagne Novembre 2021",
                "brief": "cette Campagne est un test",
                "goal": 50,
                "finish_at": "@string@.isDateTime()",
                "team": {
                    "name": "Deuxième équipe de phoning",
                    "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "members_count": 4,
                    "visibility": "national",
                    "zone": null
                },
                "audience": {
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "zones": [
                        {
                            "uuid": "e3efe563-906e-11eb-a875-0242ac150002",
                            "code": "75",
                            "name": "Paris",
                            "created_at": "@string@.isDateTime()",
                            "updated_at": "@string@.isDateTime()"
                        }
                    ],
                    "first_name": "john",
                    "last_name": "Doe",
                    "gender": "male",
                    "age_min": 30,
                    "age_max": 80,
                    "registered_since": "@string@.isDateTime()",
                    "registered_until": null,
                    "is_committee_member": false,
                    "is_certified": true,
                    "has_email_subscription": null,
                    "has_sms_subscription": true
                },
                "survey": {
                    "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Questionnaire national numéro 1"
                },
                "permanent": false,
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "creator": "Referent R.",
                "nb_calls": 0,
                "nb_surveys": 0,
                "participants_count": 0,
                "nb_adherents_called": 0,
                "visibility": "national",
                "zone": null
            }
            """

    Scenario: As a phoning national manager I cannot create a phoning campaign without the title the goal or the survey
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/phoning_campaigns?scope=phoning_national_manager" with body:
            """
            {
                "brief": "cette Campagne est un test",
                "finish_at": "+10 days",
                "team": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                "audience": {
                    "gender": "male",
                    "firstName": "john",
                    "lastName": "Doe",
                    "ageMin": 30,
                    "ageMax": 80,
                    "registeredSince": "2020-11-08T10:25:20.677Z",
                    "isCertified": true,
                    "isCommitteeMember": false,
                    "hasSmsSubscription": false,
                    "zones": ["e3efe563-906e-11eb-a875-0242ac150002"]
                }
            }
            """
        Then the response status code should be 400
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

    Scenario: As a phoning national manager I can update a phoning campaign
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387?scope=phoning_national_manager" with body:
            """
            {
                "title": "Campagne pour les femmes 2021",
                "brief": "cette Campagne est un test",
                "goal": 50,
                "finish_at": "+10 days",
                "team": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                "audience": {
                    "gender": "female",
                    "firstName": "Jane",
                    "lastName": "SMITH",
                    "ageMin": 25,
                    "ageMax": 60,
                    "registeredSince": "2020-11-08T10:25:20.677Z",
                    "isCertified": true,
                    "isCommitteeMember": false,
                    "hasSmsSubscription": false,
                    "zones": ["e3efe563-906e-11eb-a875-0242ac150002"]
                },
                "survey": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9"
            }
            """
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "title": "Campagne pour les femmes 2021",
                "brief": "cette Campagne est un test",
                "goal": 50,
                "finish_at": "@string@.isDateTime()",
                "team": {
                    "name": "Deuxième équipe de phoning",
                    "uuid": "6434f2ac-edd0-412a-9c4b-99ab4b039146",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "members_count": 4,
                    "visibility": "national",
                    "zone": null
                },
                "audience": {
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "zones": [
                        {
                            "uuid": "e3efe563-906e-11eb-a875-0242ac150002",
                            "code": "75",
                            "name": "Paris",
                            "created_at": "@string@.isDateTime()",
                            "updated_at": "@string@.isDateTime()"
                        }
                    ],
                    "first_name": "Jane",
                    "last_name": "SMITH",
                    "gender": "female",
                    "age_min": 25,
                    "age_max": 60,
                    "registered_since": "@string@.isDateTime()",
                    "registered_until": null,
                    "is_committee_member": false,
                    "is_certified": true,
                    "has_email_subscription": null,
                    "has_sms_subscription": true
                },
                "survey": {
                    "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Questionnaire national numéro 1"
                },
                "permanent": false,
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "creator": "Admin",
                "nb_calls": 12,
                "nb_surveys": 6,
                "participants_count": 0,
                "nb_adherents_called": 8,
                "visibility": "national",
                "zone": null
            }
            """

    Scenario: As a phoning national manager I can get the list of phoning campaign histories
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaign_histories?scope=phoning_national_manager"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
              "metadata": {
                "total_items": 23,
                "items_per_page": 2,
                "count": 2,
                "current_page": 1,
                "last_page": 12
              },
              "items": [
                {
                  "caller": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "gender": "male",
                    "age":@integer@
                  },
                  "adherent": {
                    "uuid": "@uuid@",
                    "first_name": "Adherent 39",
                    "last_name": "Fa39ke",
                    "gender": "male",
                    "age":@integer@
                  },
                  "campaign": {
                    "title": "Campagne pour les hommes",
                    "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
                  },
                  "type": null,
                  "status": "send",
                  "postal_code_checked": null,
                  "profession": null,
                  "need_email_renewal": null,
                  "need_sms_renewal": null,
                  "engagement": null,
                  "note": null,
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": null,
                  "data_survey": null,
                  "uuid": "@uuid@"
                },
                {
                  "caller": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "gender": "male",
                    "age":@integer@
                  },
                  "adherent": {
                    "uuid": "@uuid@",
                    "first_name": "Adrien",
                    "last_name": "Petit",
                    "gender": "male",
                    "age":@integer@
                  },
                  "campaign": {
                    "title": "Campagne pour les hommes",
                    "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
                  },
                  "type": null,
                  "status": "to-unsubscribe",
                  "postal_code_checked": null,
                  "profession": null,
                  "need_email_renewal": null,
                  "need_sms_renewal": null,
                  "engagement": null,
                  "note": null,
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": null,
                  "data_survey": {
                    "survey": {
                      "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                      "name": "Questionnaire national numéro 1"
                    },
                    "uuid": "@uuid@"
                  },
                  "uuid": "@uuid@"
                }
              ]
            }
            """

    Scenario Outline: As a phoning national manager I can get the list of phoning campaign histories filtered by campaign title or uuid
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "<url>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
               "metadata": {
                 "total_items": 12,
                 "items_per_page": 2,
                 "count": 2,
                 "current_page": 1,
                 "last_page": 6
               },
               "items": [
                 {
                   "caller": {
                     "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                     "first_name": "Jacques",
                     "last_name": "Picard",
                     "gender": "male",
                     "age":@integer@
                   },
                   "adherent": {
                     "uuid": "@uuid@",
                     "first_name": "Adherent 39",
                     "last_name": "Fa39ke",
                     "gender": "male",
                     "age":@integer@
                   },
                   "campaign": {
                     "title": "Campagne pour les hommes",
                     "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
                   },
                   "type": null,
                   "status": "send",
                   "postal_code_checked": null,
                   "profession": null,
                   "need_email_renewal": null,
                   "need_sms_renewal": null,
                   "engagement": null,
                   "note": null,
                   "begin_at": "@string@.isDateTime()",
                   "finish_at": null,
                   "data_survey": null,
                   "uuid": "@uuid@"
                 },
                 {
                   "caller": {
                     "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                     "first_name": "Jacques",
                     "last_name": "Picard",
                     "gender": "male",
                     "age":@integer@
                   },
                   "adherent": {
                     "uuid": "@uuid@",
                     "first_name": "Adrien",
                     "last_name": "Petit",
                     "gender": "male",
                     "age":@integer@
                   },
                   "campaign": {
                     "title": "Campagne pour les hommes",
                     "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
                   },
                   "type": null,
                   "status": "to-unsubscribe",
                   "postal_code_checked": null,
                   "profession": null,
                   "need_email_renewal": null,
                   "need_sms_renewal": null,
                   "engagement": null,
                   "note": null,
                   "begin_at": "@string@.isDateTime()",
                   "finish_at": null,
                   "data_survey": {
                     "survey": {
                       "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                       "name": "Questionnaire national numéro 1"
                     },
                     "uuid": "@uuid@"
                   },
                   "uuid": "@uuid@"
                 }
               ]
             }
            """

        Examples:
            | url                                                                                                                  |
            | /api/v3/phoning_campaign_histories?scope=phoning_national_manager&campaign.title=campagne%20pour%20les%20hommes      |
            | /api/v3/phoning_campaign_histories?scope=phoning_national_manager&campaign.uuid=4ebb184c-24d9-4aeb-bb36-afe44f294387 |

    Scenario: As a phoning national manager I can get the list of phoning campaign histories filtered by caller
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaign_histories?scope=phoning_national_manager&caller=Pierre"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
              "metadata": {
                "total_items": 7,
                "items_per_page": 2,
                "count": 2,
                "current_page": 1,
                "last_page": 4
              },
              "items": [
                {
                  "caller": {
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "gender": "male",
                    "age":@integer@
                  },
                  "adherent": {
                    "uuid": "@uuid@",
                    "first_name": "Adherent 35",
                    "last_name": "Fa35ke",
                    "gender": "male",
                    "age":@integer@
                  },
                  "campaign": {
                    "title": "Campagne pour les hommes",
                    "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
                  },
                  "type": null,
                  "status": "interrupted-dont-remind",
                  "postal_code_checked": null,
                  "profession": null,
                  "need_email_renewal": null,
                  "need_sms_renewal": null,
                  "engagement": null,
                  "note": null,
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": null,
                  "data_survey": {
                    "survey": {
                      "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                      "name": "Questionnaire national numéro 1"
                    },
                    "uuid": "@uuid@"
                  },
                  "uuid": "@uuid@"
                },
                {
                  "caller": {
                    "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
                    "first_name": "Pierre",
                    "last_name": "Kiroule",
                    "gender": "male",
                    "age":@integer@
                  },
                  "adherent": {
                    "uuid": "@uuid@",
                    "first_name": "Adherent 41",
                    "last_name": "Fa41ke",
                    "gender": "male",
                    "age":@integer@
                  },
                  "campaign": {
                    "title": "Campagne pour les hommes",
                    "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
                  },
                  "type": null,
                  "status": "not-respond",
                  "postal_code_checked": null,
                  "profession": null,
                  "need_email_renewal": null,
                  "need_sms_renewal": null,
                  "engagement": null,
                  "note": null,
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": null,
                  "data_survey": null,
                  "uuid": "@uuid@"
                }
              ]
            }
            """

    Scenario: As a phoning national manager I can get the list of phoning campaign histories filtered by adherent
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaign_histories?scope=phoning_national_manager&adherent=Adrien"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
              "metadata": {
                "total_items": 1,
                "items_per_page": 2,
                "count": 1,
                "current_page": 1,
                "last_page": 1
              },
              "items": [
                {
                  "caller": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "gender": "male",
                    "age":@integer@
                  },
                  "adherent": {
                    "uuid": "@uuid@",
                    "first_name": "Adrien",
                    "last_name": "Petit",
                    "gender": "male",
                    "age":@integer@
                  },
                  "campaign": {
                    "title": "Campagne pour les hommes",
                    "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
                  },
                  "type": null,
                  "status": "to-unsubscribe",
                  "postal_code_checked": null,
                  "profession": null,
                  "need_email_renewal": null,
                  "need_sms_renewal": null,
                  "engagement": null,
                  "note": null,
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": null,
                  "data_survey": {
                    "survey": {
                      "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                      "name": "Questionnaire national numéro 1"
                    },
                    "uuid": "@uuid@"
                  },
                  "uuid": "@uuid@"
                }
              ]
            }
            """

    Scenario: As a phoning national manager I can get the list of phoning campaign histories filtered by status
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaign_histories?scope=phoning_national_manager&status=to-unsubscribe"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
              "metadata": {
                "total_items": 2,
                "items_per_page": 2,
                "count": 2,
                "current_page": 1,
                "last_page": 1
              },
              "items": [
                {
                  "caller": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "gender": "male",
                    "age":@integer@
                  },
                  "adherent": {
                    "uuid": "@uuid@",
                    "first_name": "Adrien",
                    "last_name": "Petit",
                    "gender": "male",
                    "age":@integer@
                  },
                  "campaign": {
                    "title": "Campagne pour les hommes",
                    "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
                  },
                  "type": null,
                  "status": "to-unsubscribe",
                  "postal_code_checked": null,
                  "profession": null,
                  "need_email_renewal": null,
                  "need_sms_renewal": null,
                  "engagement": null,
                  "note": null,
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": null,
                  "data_survey": {
                    "survey": {
                      "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                      "name": "Questionnaire national numéro 1"
                    },
                    "uuid": "@uuid@"
                  },
                  "uuid": "@uuid@"
                },
                {
                  "caller": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "gender": "male",
                    "age":@integer@
                  },
                  "adherent": {
                    "uuid": "@uuid@",
                    "first_name": "Étienne",
                    "last_name": "Petit",
                    "gender": "male",
                    "age":@integer@
                  },
                  "campaign": {
                    "title": "Campagne pour les femmes",
                    "uuid": "4d91b94c-4b39-43c7-9c88-f4be7e2fe0bc"
                  },
                  "type": null,
                  "status": "to-unsubscribe",
                  "postal_code_checked": null,
                  "profession": null,
                  "need_email_renewal": null,
                  "need_sms_renewal": null,
                  "engagement": null,
                  "note": null,
                  "begin_at": "@string@.isDateTime()",
                  "finish_at": null,
                  "data_survey": {
                    "survey": {
                      "uuid": "13814039-1dd2-11b2-9bfd-78ea3dcdf0d9",
                      "name": "Questionnaire national numéro 1"
                    },
                    "uuid": "@uuid@"
                  },
                  "uuid": "@uuid@"
                }
              ]
            }
            """

    Scenario: As a phoning national manager I can get the list of phoning campaign histories filtered by begin date
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaign_histories?scope=phoning_national_manager&beginAt[after]=2021-07-01&beginAt[before]=2021-07-31"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
              "metadata": {
                "total_items": 1,
                "items_per_page": 2,
                "count": 1,
                "current_page": 1,
                "last_page": 1
              },
              "items": [
                {
                  "caller": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard",
                    "gender": "male",
                    "age":@integer@
                  },
                  "adherent": {
                    "uuid": "@uuid@",
                    "first_name": "Guillaume",
                    "last_name": "Richard",
                    "gender": "male",
                    "age":@integer@
                  },
                  "campaign": {
                    "title": "Campagne pour les hommes",
                    "uuid": "4ebb184c-24d9-4aeb-bb36-afe44f294387"
                  },
                  "type": null,
                  "status": "failed",
                  "postal_code_checked": null,
                  "profession": null,
                  "need_email_renewal": null,
                  "need_sms_renewal": null,
                  "engagement": null,
                  "note": null,
                  "begin_at": "2021-07-14T00:00:00+02:00",
                  "finish_at": null,
                  "data_survey": null,
                  "uuid": "@uuid@"
                }
              ]
            }
            """

    Scenario Outline: As a phoning national manager I can get a phoning campaign callers with their stats
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaigns/4ebb184c-24d9-4aeb-bb36-afe44f294387/callers?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "firstName": "Jacques",
                    "lastName": "Picard",
                    "nb_calls": 5,
                    "nb_surveys": 4,
                    "nb_completed": "1",
                    "nb_to_unsubscribe": "1",
                    "nb_to_unjoin": "1",
                    "nb_to_remind": "0",
                    "nb_not_respond": "0",
                    "nb_failed": "1"
                },
                {
                    "firstName": "Pierre",
                    "lastName": "Kiroule",
                    "nb_calls": 4,
                    "nb_surveys": 2,
                    "nb_completed": "0",
                    "nb_to_unsubscribe": "0",
                    "nb_to_unjoin": "0",
                    "nb_to_remind": "1",
                    "nb_not_respond": "2",
                    "nb_failed": "0"
                },
                {
                    "firstName": "Michelle",
                    "lastName": "Dufour",
                    "nb_calls": 0,
                    "nb_surveys": 0,
                    "nb_completed": "0",
                    "nb_to_unsubscribe": "0",
                    "nb_to_unjoin": "0",
                    "nb_to_remind": "0",
                    "nb_not_respond": "0",
                    "nb_failed": "0"
                }
            ]
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | phoning_national_manager                       |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a phoning national manager I can get the list of a campaign replies
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaigns/9ca189b7-7635-4c3a-880b-6ce5cd10e8bc/replies?scope=phoning_national_manager&page=1&page_size=10"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 3,
                    "items_per_page": 10,
                    "count": 3,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "survey": {
                            "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                            "name": "Les enjeux des 10 prochaines années"
                        },
                        "phoning_campaign_history": {
                            "caller": {
                                "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                                "first_name": "Lucie",
                                "last_name": "Olivera",
                                "gender": "female",
                                "age": @integer@
                            },
                            "adherent": {
                                "uuid": "@uuid@",
                                "first_name": "Adherent 34",
                                "last_name": "Fa34ke",
                                "gender": "female",
                                "age": @integer@
                            },
                            "campaign": {
                                "title": "Campagne sur l'horizon 2030",
                                "uuid": "9ca189b7-7635-4c3a-880b-6ce5cd10e8bc"
                            },
                            "begin_at": "@string@.isDateTime()",
                            "finish_at": "@string@.isDateTime()",
                            "uuid": "5587ce1f-bf4d-486f-a356-e75b06a62e2e"
                        },
                        "uuid": "@uuid@",
                        "answers": [
                            {
                                "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                                "type": "simple_field",
                                "question_id": @integer@,
                                "answer": "le pouvoir d'achat"
                            },
                            {
                                "question": "L'écologie est selon vous, importante pour :",
                                "type": "multiple_choice",
                                "question_id": @integer@,
                                "answer": [
                                    "L'aspect financier",
                                    "La préservation de l'environnement"
                                ]
                            }
                        ]
                    },
                    {
                        "uuid": "@uuid@",
                        "survey": {
                            "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                            "name": "Les enjeux des 10 prochaines années"
                        },
                        "phoning_campaign_history": {
                            "caller": {
                                "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                                "first_name": "Lucie",
                                "last_name": "Olivera",
                                "gender": "female",
                                "age": @integer@
                            },
                            "adherent": {
                                "uuid": "@uuid@",
                                "first_name": "Adherent 37",
                                "last_name": "Fa37ke",
                                "gender": "male",
                                "age": @integer@
                            },
                            "campaign": {
                                "title": "Campagne sur l'horizon 2030",
                                "uuid": "9ca189b7-7635-4c3a-880b-6ce5cd10e8bc"
                            },
                            "begin_at": "@string@.isDateTime()",
                            "finish_at": "@string@.isDateTime()",
                            "uuid": "e369f31b-d339-4ba7-b303-baa980c430cc"
                        },
                        "answers": [
                            {
                                "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                                "type": "simple_field",
                                "question_id": @integer@,
                                "answer": "la conquête de l'espace"
                            },
                            {
                                "question": "L'écologie est selon vous, importante pour :",
                                "type": "multiple_choice",
                                "question_id": @integer@,
                                "answer": [
                                    "L'héritage laissé aux générations futures",
                                    "Le bien-être sanitaire"
                                ]
                            }
                        ]
                    },
                    {
                        "uuid": "@uuid@",
                        "survey": {
                            "uuid": "4c3594d4-fb6f-4e25-ac2e-7ef81694ec47",
                            "name": "Les enjeux des 10 prochaines années"
                        },
                        "phoning_campaign_history": {
                            "caller": {
                                "uuid": "29461c49-6316-5be1-9ac3-17816bf2d819",
                                "first_name": "Lucie",
                                "last_name": "Olivera",
                                "gender": "female",
                                "age": @integer@
                            },
                            "adherent": {
                                "uuid": "@uuid@",
                                "first_name": "Adherent 40",
                                "last_name": "Fa40ke",
                                "gender": "female",
                                "age": @integer@
                            },
                            "campaign": {
                                "title": "Campagne sur l'horizon 2030",
                                "uuid": "9ca189b7-7635-4c3a-880b-6ce5cd10e8bc"
                            },
                            "begin_at": "@string@.isDateTime()",
                            "finish_at": "@string@.isDateTime()",
                            "uuid": "b3c51626-164f-4fbd-9109-e70b20ab5788"
                        },
                        "answers": [
                            {
                                "question": "A votre avis quels seront les enjeux des 10 prochaines années?",
                                "type": "simple_field",
                                "question_id": @integer@,
                                "answer": "l'écologie sera le sujet le plus important"
                            },
                            {
                                "question": "L'écologie est selon vous, importante pour :",
                                "type": "multiple_choice",
                                "question_id": @integer@,
                                "answer": [
                                    "L'héritage laissé aux générations futures",
                                    "Le bien-être sanitaire"
                                ]
                            }
                        ]
                    }
                ]
            }
            """

    Scenario: As a phoning national manager I can get phoning campaigns KPI
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaigns/kpi?scope=phoning_national_manager"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "nb_campaigns": 7,
                "nb_ongoing_campaigns": 6,
                "nb_calls": 19,
                "nb_calls_last_30d": 18,
                "nb_surveys": 14,
                "nb_surveys_last_30d": 14
            }
            """

    Scenario Outline: As a (delegated) referent I can get phoning campaigns KPI
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaigns/kpi?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "nb_campaigns": 2,
                "nb_ongoing_campaigns": 2,
                "nb_calls": 0,
                "nb_calls_last_30d": 0,
                "nb_surveys": 0,
                "nb_surveys_last_30d": 0
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a phoning national manager referent I can get a phoning campaign details with the calling time average
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/phoning_campaigns/9ca189b7-7635-4c3a-880b-6ce5cd10e8bc?scope=phoning_national_manager&page_size=10"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "title": "Campagne sur l'horizon 2030",
                "brief": "### Décidons aujourd'hui de la France de demain",
                "goal": 500,
                "finish_at": "@string@.isDateTime()",
                "visibility": "national",
                "zone": null,
                "team": {
                    "name": "Deuxième équipe de phoning",
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "members_count": 4,
                    "visibility": "national",
                    "zone": null
                },
                "audience": {
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "zones": [],
                    "first_name": null,
                    "last_name": null,
                    "gender": null,
                    "age_min": 18,
                    "age_max": 80,
                    "registered_since": null,
                    "registered_until": null,
                    "is_committee_member": null,
                    "is_certified": null,
                    "has_email_subscription": null,
                    "has_sms_subscription": null
                },
                "survey": {
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Les enjeux des 10 prochaines années"
                },
                "permanent": false,
                "uuid": "@uuid@",
                "created_at": "@string@.isDateTime()",
                "updated_at": "@string@.isDateTime()",
                "creator": "Admin",
                "nb_calls": 4,
                "nb_surveys": 3,
                "participants_count": 0,
                "nb_adherents_called": 4,
                "nb_un_join": 0,
                "nb_un_subscribe": 0,
                "to_remind": 0,
                "not_respond": 0,
                "nb_failed": 1,
                "average_calling_time": 400,
                "visibility": "national",
                "zone": null
            }
            """
