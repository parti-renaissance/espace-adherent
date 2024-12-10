@api
@renaissance
Feature:
    In order to complete PAP campaigns
    I should be able to retrieve addresses for a given position and additional data

    Scenario Outline: As an anonymous I can not get address and voters information
        When I send a "GET" request to "<url>"
        Then the response status code should be 401

        Examples:
            | url                                                                                                       |
            | /api/v3/pap/address/near?latitude=48.879001640&longitude=2.3187434&zoom=15                                |
            | /api/v3/pap/address/near?latitude=48.879001640&longitude=2.3187434&latitudeDelta=0.02&longitudeDelta=0.01 |
            | /api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f                                                  |
            | /api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f/voters                                           |
            | /api/v3/pap/buildings/faf30370-80c5-4a46-8c31-f6a361bfa23b/building_blocks                                |
            | /api/v3/pap/buildings/2bffd913-34fe-48ad-95f4-7381812b93dd/history                                        |

    Scenario Outline: As a logged-in user with no PAP user role I cannot get and manage PAP campaigns
        Given I am logged with "deputy-75-2@en-marche-dev.fr" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        And I send a "<method>" request to "<url>"
        Then the response status code should be 403

        Examples:
            | method | url                                                                        |
            | GET    | /api/v3/pap/buildings/faf30370-80c5-4a46-8c31-f6a361bfa23b/building_blocks |
            | POST   | /api/v3/pap/buildings/2fbe7b02-944d-4abd-be3d-f9b2944917a9/events          |
            | GET    | /api/v3/pap/buildings/2bffd913-34fe-48ad-95f4-7381812b93dd/history         |
            | PUT    | /api/v3/pap/buildings/2bffd913-34fe-48ad-95f4-7381812b93dd                 |

    Scenario Outline: As a logged-in user I can retrieve addresses near a given position ordered by distance
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/pap/address/near?latitude=<latitude>&longitude=<longitude>&latitudeDelta=<latitudeDelta>&longitudeDelta=<longitudeDelta>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            <addresses>
            """

        Examples:
            | latitude     | longitude | latitudeDelta | longitudeDelta | addresses                                                                                                                                                                                                |
            # 68 rue du rocher, Paris 8ème => 65, 70, 67, 55 rue du rocher
            | 48.879001640 | 2.3187434 | 0.02          | 0.01           | [{"uuid": "ccfd846a-5439-42ad-85ce-286baf4e7269"}, {"uuid": "04e1d76f-c727-4612-afab-2dec2d71a480"}, {"uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"}, {"uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f"}] |
            # 54 rue du rocher, Paris 8ème => 55, 65, 70, 67 rue du rocher
            | 48.877018    | 2.32154   | 0.04          | 0.02           | [{"uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f"}, {"uuid": "ccfd846a-5439-42ad-85ce-286baf4e7269"}, {"uuid": "04e1d76f-c727-4612-afab-2dec2d71a480"}, {"uuid": "702eda29-39c6-4b3d-b28f-3fd3806747b2"}] |

    Scenario: As a logged-in user I can retrieve latitude & longitude of addresses near a given position
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        # 62 rue du rocher, Paris 8ème
        When I send a "GET" request to "/api/v3/pap/address/near?latitude=48.877018&longitude=2.32154&zoom=16"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            # 55, 65, 70, 67 rue du rocher, 62 Rue de Rome, Paris 8ème
            """
            [
                {
                    "number": "55",
                    "priority": null,
                    "address": "Rue du Rocher",
                    "insee_code": "75108",
                    "postal_codes": ["75008"],
                    "city_name": "Paris 8ème",
                    "latitude": 48.878708,
                    "longitude": 2.319111,
                    "voters_count": 2,
                    "building": {
                        "type": "building",
                        "uuid": "@uuid@",
                        "campaign_statistics": {
                            "campaign": {
                                "uuid": "@uuid@"
                            },
                            "status": "ongoing",
                            "status_detail": null,
                            "last_passage": "@string@.isDateTime()",
                            "last_passage_done_by": {
                                "uuid": "@uuid@",
                                "first_name": "Adherent 33",
                                "last_name": "Fa33ke"
                            },
                            "nb_visited_doors": 1,
                            "nb_distributed_programs": 0,
                            "nb_surveys": 0,
                            "uuid": "@uuid@"
                        }
                    },
                    "uuid": "@uuid@"
                },
                {
                    "number": "65",
                    "priority": null,
                    "address": "Rue du Rocher",
                    "insee_code": "75108",
                    "postal_codes": ["75008"],
                    "city_name": "Paris 8ème",
                    "latitude": 48.879078,
                    "longitude": 2.318631,
                    "voters_count": 1,
                    "building": {
                        "type": "building",
                        "uuid": "@uuid@",
                        "campaign_statistics": {
                            "campaign": {
                                "uuid": "@uuid@"
                            },
                            "status": "todo",
                            "status_detail": null,
                            "last_passage": null,
                            "last_passage_done_by": null,
                            "nb_visited_doors": 0,
                            "nb_distributed_programs": 0,
                            "nb_surveys": 0,
                            "uuid": "@uuid@"
                        }
                    },
                    "uuid": "@uuid@"
                },
                {
                    "number": "70",
                    "priority": null,
                    "address": "Rue du Rocher",
                    "insee_code": "75108",
                    "postal_codes": ["75008"],
                    "voters_count": 1,
                    "city_name": "Paris 8ème",
                    "latitude": 48.879166,
                    "longitude": 2.318761,
                    "building": {
                        "type": "building",
                        "uuid": "@uuid@",
                        "campaign_statistics": {
                            "campaign": {
                                "uuid": "@uuid@"
                            },
                            "status": "todo",
                            "status_detail": null,
                            "last_passage": null,
                            "last_passage_done_by": null,
                            "nb_visited_doors": 0,
                            "nb_distributed_programs": 0,
                            "nb_surveys": 0,
                            "uuid": "@uuid@"
                        }
                    },
                    "uuid": "@uuid@"
                },
                {
                    "number": "67",
                    "priority": null,
                    "address": "Rue du Rocher",
                    "insee_code": "75108",
                    "postal_codes": ["75008"],
                    "voters_count": 3,
                    "city_name": "Paris 8ème",
                    "latitude": 48.879246,
                    "longitude": 2.318427,
                    "building": {
                        "type": "building",
                        "uuid": "@uuid@",
                        "campaign_statistics": {
                            "campaign": {
                                "uuid": "@uuid@"
                            },
                            "status": "todo",
                            "status_detail": null,
                            "last_passage": null,
                            "last_passage_done_by": null,
                            "nb_visited_doors": 0,
                            "nb_distributed_programs": 0,
                            "nb_surveys": 0,
                            "uuid": "@uuid@"
                        }
                    },
                    "uuid": "@uuid@"
                },
                {
                    "address": "Rue de Rome",
                    "building": {
                        "campaign_statistics": {
                            "campaign": {
                                "uuid": "08463014-bbfe-421c-b8fb-5e456414b088"
                            },
                            "last_passage": null,
                            "last_passage_done_by": null,
                            "nb_surveys": 0,
                            "nb_visited_doors": 0,
                            "nb_distributed_programs": 0,
                            "status": "todo",
                            "status_detail": null,
                            "uuid": "@uuid@"
                        },
                        "type": "building",
                        "uuid": "88285b14-038c-4305-8e0c-3fa66d330169"
                    },
                    "city_name": "Paris 8ème",
                    "insee_code": "75108",
                    "latitude": 48.880085,
                    "longitude": 2.321696,
                    "number": "62",
                    "priority": null,
                    "postal_codes": ["75008"],
                    "uuid": "f93d880e-5d8c-4e6f-bfc8-3b93d8131437",
                    "voters_count": 1
                }
            ]
            """

    Scenario: As a logged-in user I can retrieve full address information for a given address identifier
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f",
                "number": "55",
                "priority": null,
                "address": "Rue du Rocher",
                "insee_code": "75108",
                "postal_codes": ["75008"],
                "city_name": "Paris 8ème",
                "latitude": 48.878708,
                "longitude": 2.319111,
                "building": {
                    "type": "building",
                    "uuid": "@uuid@",
                    "campaign_statistics": {
                        "campaign": {
                            "uuid": "@uuid@"
                        },
                        "status": "ongoing",
                        "status_detail": null,
                        "last_passage": "@string@.isDateTime()",
                        "last_passage_done_by": {
                            "uuid": "@uuid@",
                            "first_name": "Adherent 33",
                            "last_name": "Fa33ke"
                        },
                        "nb_visited_doors": 1,
                        "nb_distributed_programs": 0,
                        "nb_surveys": 0,
                        "uuid": "@uuid@"
                    }
                },
                "voters_count": 2
            }
            """

    Scenario: As a logged-in user I can retrieve the voter list for a given address identifier
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f/voters"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "uuid": "bdb9d49c-20f5-44c0-bc4a-d8b75f85ee95",
                    "first_name": "J.",
                    "last_name": "Doe",
                    "gender": "male",
                    "birthdate": "@string@.isDateTime()",
                    "vote_place": "75108_0001"
                },
                {
                    "uuid": "0cf560f0-c5ec-43ef-9ea1-b6fd2a2dc339",
                    "first_name": "J.",
                    "last_name": "Doe",
                    "gender": "female",
                    "birthdate": "@string@.isDateTime()",
                    "vote_place": "75108_0001"
                }
            ]
            """

    Scenario: As a logged-in user I can retrieve the building block list for a given building identifier
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/pap/buildings/faf30370-80c5-4a46-8c31-f6a361bfa23b/building_blocks"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "uuid": "@uuid@",
                    "name": "A",
                    "floors": [
                        {
                            "uuid": "@uuid@",
                            "number": 0
                        },
                        {
                            "uuid": "@uuid@",
                            "number": 1
                        }
                    ]
                },
                {
                    "uuid": "@uuid@",
                    "name": "B",
                    "floors": [
                        {
                            "uuid": "@uuid@",
                            "number": 0
                        },
                        {
                            "uuid": "@uuid@",
                            "number": 1
                        }
                    ]
                }
            ]
            """

    Scenario: As a logged-in user I can retrieve the building block list for a given building identifier for a PAP campaign
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/pap/buildings/faf30370-80c5-4a46-8c31-f6a361bfa23b/building_blocks?campaign_uuid=d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "uuid": "@uuid@",
                    "name": "A",
                    "campaign_statistics": {
                        "status": "completed",
                        "closed_at": "@string@.isDateTime()",
                        "closed_by": "Adherent 32 F."
                    },
                    "floors": [
                        {
                            "uuid": "@uuid@",
                            "number": 0,
                            "campaign_statistics": {
                                "status": "completed",
                                "visited_doors": [],
                                "nb_surveys": 0,
                                "closed_at": "@string@.isDateTime()",
                                "closed_by": "Adherent 32 F."
                            }
                        },
                        {
                            "uuid": "@uuid@",
                            "number": 1,
                            "campaign_statistics": {
                                "status": "completed",
                                "visited_doors": [],
                                "nb_surveys": 0,
                                "closed_at": "@string@.isDateTime()",
                                "closed_by": "Adherent 32 F."
                            }
                        }
                    ]
                },
                {
                    "uuid": "@uuid@",
                    "name": "B",
                    "campaign_statistics": {
                        "status": "ongoing",
                        "closed_at": null,
                        "closed_by": null
                    },
                    "floors": [
                        {
                            "uuid": "@uuid@",
                            "number": 0,
                            "campaign_statistics": {
                                "status": "ongoing",
                                "visited_doors": [],
                                "nb_surveys": 0,
                                "closed_at": null,
                                "closed_by": null
                            }
                        },
                        {
                            "uuid": "@uuid@",
                            "number": 1,
                            "campaign_statistics": {
                                "status": "ongoing",
                                "visited_doors": [],
                                "nb_surveys": 0,
                                "closed_at": null,
                                "closed_by": null
                            }
                        }
                    ]
                }
            ]
            """

    Scenario: As a logged-in user I can open and close a building
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/pap/buildings/2fbe7b02-944d-4abd-be3d-f9b2944917a9/events" with body:
            """
            {
                "action": "open",
                "type": "building",
                "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "OK"
            """
        When I send a "GET" request to "/api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f"
        And I wait 1 second
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f",
                "number": "55",
                "priority": null,
                "address": "Rue du Rocher",
                "insee_code": "75108",
                "postal_codes": ["75008"],
                "city_name": "Paris 8ème",
                "latitude": 48.878708,
                "longitude": 2.319111,
                "building": {
                    "type": "building",
                    "uuid": "@uuid@",
                    "campaign_statistics": {
                        "campaign": {
                            "uuid": "@uuid@"
                        },
                        "status": "ongoing",
                        "status_detail": null,
                        "last_passage": "@string@.isDateTime()",
                        "last_passage_done_by": {
                            "uuid": "@uuid@",
                            "first_name": "Adherent 33",
                            "last_name": "Fa33ke"
                        },
                        "nb_visited_doors": 1,
                        "nb_distributed_programs": 0,
                        "nb_surveys": 0,
                        "uuid": "@uuid@"
                    }
                },
                "voters_count": 2
            }
            """
        When I send a "POST" request to "/api/v3/pap/buildings/2fbe7b02-944d-4abd-be3d-f9b2944917a9/events" with body:
            """
            {
                "action": "close",
                "type": "building",
                "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "OK"
            """
        When I send a "GET" request to "/api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f"
        And I wait 1 second
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f",
                "number": "55",
                "priority": null,
                "address": "Rue du Rocher",
                "insee_code": "75108",
                "postal_codes": ["75008"],
                "city_name": "Paris 8ème",
                "latitude": 48.878708,
                "longitude": 2.319111,
                "building": {
                    "type": "building",
                    "uuid": "@uuid@",
                    "campaign_statistics": {
                        "campaign": {
                            "uuid": "@uuid@"
                        },
                        "status": "completed",
                        "status_detail": "completed_pap",
                        "last_passage": "@string@.isDateTime()",
                        "last_passage_done_by": {
                            "uuid": "@uuid@",
                            "first_name": "Adherent 33",
                            "last_name": "Fa33ke"
                        },
                        "nb_visited_doors": 1,
                        "nb_distributed_programs": 0,
                        "nb_surveys": 0,
                        "uuid": "@uuid@"
                    }
                },
                "voters_count": 2
            }
            """
        When I send a "POST" request to "/api/v3/pap/buildings/2fbe7b02-944d-4abd-be3d-f9b2944917a9/events" with body:
            """
            {
                "action": "close",
                "close_type": "boitage",
                "programs": 10,
                "type": "building",
                "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "OK"
            """
        When I send a "GET" request to "/api/v3/pap/address/a0b9231b-9ff5-49b9-aa7a-1d28abbba32f"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "a0b9231b-9ff5-49b9-aa7a-1d28abbba32f",
                "number": "55",
                "priority": null,
                "address": "Rue du Rocher",
                "insee_code": "75108",
                "postal_codes": ["75008"],
                "city_name": "Paris 8ème",
                "latitude": 48.878708,
                "longitude": 2.319111,
                "building": {
                    "type": "building",
                    "uuid": "@uuid@",
                    "campaign_statistics": {
                        "campaign": {
                            "uuid": "@uuid@"
                        },
                        "status": "completed",
                        "status_detail": "completed_hybrid",
                        "last_passage": "@string@.isDateTime()",
                        "last_passage_done_by": {
                            "uuid": "@uuid@",
                            "first_name": "Adherent 33",
                            "last_name": "Fa33ke"
                        },
                        "nb_visited_doors": 1,
                        "nb_distributed_programs": 10,
                        "nb_surveys": 0,
                        "uuid": "@uuid@"
                    }
                },
                "voters_count": 2
            }
            """

    Scenario: As a logged-in user I can open and close a building block
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/pap/buildings/2fbe7b02-944d-4abd-be3d-f9b2944917a9/events" with body:
            """
            {
                "action": "open",
                "type": "building_block",
                "identifier": "A",
                "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "OK"
            """
        When I send a "POST" request to "/api/v3/pap/buildings/2fbe7b02-944d-4abd-be3d-f9b2944917a9/events" with body:
            """
            {
                "action": "close",
                "type": "building_block",
                "identifier": "A",
                "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "OK"
            """

    Scenario: As a logged-in user I can open and close a floor
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "POST" request to "/api/v3/pap/buildings/2fbe7b02-944d-4abd-be3d-f9b2944917a9/events" with body:
            """
            {
                "action": "open",
                "type": "floor",
                "identifier": "A-0",
                "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "OK"
            """
        When I send a "POST" request to "/api/v3/pap/buildings/2fbe7b02-944d-4abd-be3d-f9b2944917a9/events" with body:
            """
            {
                "action": "close",
                "type": "floor",
                "identifier": "A-0",
                "campaign": "d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "OK"
            """

    Scenario: As a logged-in user I cannot retrieve building history if no "campaign_uuid" passed
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/pap/buildings/2bffd913-34fe-48ad-95f4-7381812b93dd/history"
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            "Parameter \"campaign_uuid\" is required."
            """

    Scenario: As a logged-in user I cannot retrieve building history for invalid campaign
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/pap/buildings/2bffd913-34fe-48ad-95f4-7381812b93dd/history?campaign_uuid=d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf8"
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            "Campaign with uuid \"d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf8\" not found."
            """

    Scenario: As a logged-in user I can retrieve building history
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/pap/buildings/2bffd913-34fe-48ad-95f4-7381812b93dd/history?campaign_uuid=d0fa7f9c-e976-44ad-8a52-2a0a0d8acaf9"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "created_at": "@string@.isDateTime()",
                    "building_block": "A",
                    "floor": 0,
                    "door": "01",
                    "status_label": "Porte ouverte",
                    "questioner": {
                        "partial_name": "Jacques P."
                    }
                },
                {
                    "created_at": "@string@.isDateTime()",
                    "building_block": "A",
                    "floor": 0,
                    "door": "02",
                    "status_label": "Ne souhaite pas répondre",
                    "questioner": {
                        "partial_name": "Jacques P."
                    }
                },
                {
                    "created_at": "@string@.isDateTime()",
                    "building_block": "A",
                    "floor": 1,
                    "door": "11",
                    "status_label": "Accepte d'échanger",
                    "questioner": {
                        "partial_name": "Patrick B."
                    }
                },
                {
                    "created_at": "@string@.isDateTime()",
                    "building_block": "A",
                    "floor": 1,
                    "door": "12",
                    "status_label": "Accepte d'échanger",
                    "questioner": {
                        "partial_name": "Patrick B."
                    }
                },
                {
                    "created_at": "@string@.isDateTime()",
                    "building_block": "A",
                    "floor": 1,
                    "door": "13",
                    "status_label": "Accepte d'échanger",
                    "questioner": {
                        "partial_name": "Patrick B."
                    }
                }
            ]
            """

    Scenario: As a logged-in user I cannot change building type with invalid type
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "PUT" request to "api/v3/pap/buildings/faf30370-80c5-4a46-8c31-f6a361bfa23b" with body:
            """
            {
                "type": "invalid"
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
                        "propertyPath": "type",
                        "message": "Le type n'est pas valide."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can change building type
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "PUT" request to "api/v3/pap/buildings/faf30370-80c5-4a46-8c31-f6a361bfa23b" with body:
            """
            {
                "type": "house"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "type": "house",
                "uuid": "faf30370-80c5-4a46-8c31-f6a361bfa23b"
            }
            """
