@api
@renaissance
Feature:
    In order to get scopes of an adherent
    I should be able to request them via the API

    Scenario:
        When I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/profile/me/scopes"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "code": "deputy",
                    "name": "Délégué de circonscription",
                    "attributes": null,
                    "zones": [
                        {
                            "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                            "code": "75-1",
                            "name": "Paris (1)"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": [
                        "dashboard",
                        "contacts",
                        "messages",
                        "events",
                        "mobile_app",
                        "elections",
                        "ripostes",
                        "survey",
                        "elected_representative",
                        "adherent_formations",
                        "general_meeting_reports",
                        "documents",
                        "referrals"
                    ]
                },
                {
                    "code": "national_communication",
                    "name": "Rôle national communication",
                    "attributes": null,
                    "zones": [
                        {
                            "uuid": "e3ef8883-906e-11eb-a875-0242ac150002",
                            "code": "FR",
                            "name": "France"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["news"]
                },
                {
                    "code": "national",
                    "name": "Rôle national",
                    "attributes": null,
                    "zones": [
                        {
                            "uuid": "e3ef8883-906e-11eb-a875-0242ac150002",
                            "code": "FR",
                            "name": "France"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": [
                        "dashboard",
                        "contacts",
                        "contacts_export",
                        "messages_vox",
                        "events",
                        "my_team",
                        "mobile_app",
                        "news",
                        "elections",
                        "ripostes",
                        "pap",
                        "pap_v2",
                        "team",
                        "phoning_campaign",
                        "survey",
                        "adherent_formations",
                        "committee",
                        "general_meeting_reports",
                        "documents",
                        "designation",
                        "statutory_message",
                        "procurations",
                        "actions",
                        "featurebase",
                        "circonscriptions",
                        "referrals"
                    ]
                },
                {
                    "code": "pap_national_manager",
                    "name": "Responsable National PAP",
                    "attributes": null,
                    "zones": [
                        {
                            "uuid": "e3ef8883-906e-11eb-a875-0242ac150002",
                            "code": "FR",
                            "name": "France"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["pap"]
                },
                {
                    "code": "pap",
                    "name": "Porte-à-porteur",
                    "attributes": null,
                    "zones": [],
                    "apps": ["jemarche"],
                    "features": []
                },
                {
                    "code": "phoning_national_manager",
                    "name": "Responsable phoning",
                    "attributes": null,
                    "zones": [
                        {
                            "uuid": "e3ef8883-906e-11eb-a875-0242ac150002",
                            "code": "FR",
                            "name": "France"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["team", "phoning_campaign"]
                },
                {
                    "code": "phoning",
                    "name": "Appelant",
                    "attributes": null,
                    "zones": [],
                    "apps": ["jemarche"],
                    "features": []
                }
            ]
            """

    Scenario:
        When I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/profile/me/scope/deputy"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "code": "deputy",
                "name": "Délégué de circonscription",
                "zones": [
                    {
                        "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                        "code": "75-1",
                        "type": "district",
                        "name": "Paris (1)",
                        "latitude": null,
                        "longitude": null
                    }
                ],
                "apps": ["data_corner"],
                "features": [
                    "dashboard",
                    "contacts",
                    "messages",
                    "events",
                    "mobile_app",
                    "elections",
                    "ripostes",
                    "survey",
                    "elected_representative",
                    "adherent_formations",
                    "general_meeting_reports",
                    "documents",
                    "referrals"
                ],
                "attributes": null,
                "delegated_access": null
            }
            """

    Scenario:
        When I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/profile/me/scope/national"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "code": "national",
                "name": "Rôle national",
                "zones": [
                    {
                        "code": "FR",
                        "type": "country",
                        "name": "France",
                        "uuid": "e3ef8883-906e-11eb-a875-0242ac150002",
                        "latitude": null,
                        "longitude": null
                    }
                ],
                "apps": ["data_corner"],
                "features": [
                    "dashboard",
                    "contacts",
                    "contacts_export",
                    "messages_vox",
                    "events",
                    "my_team",
                    "mobile_app",
                    "news",
                    "elections",
                    "ripostes",
                    "pap",
                    "pap_v2",
                    "team",
                    "phoning_campaign",
                    "survey",
                    "adherent_formations",
                    "committee",
                    "general_meeting_reports",
                    "documents",
                    "designation",
                    "statutory_message",
                    "procurations",
                    "actions",
                    "featurebase",
                    "circonscriptions",
                    "referrals"
                ],
                "attributes": null,
                "delegated_access": null
            }
            """

    Scenario:
        When I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/profile/me/scopes"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "code": "delegated_96076afb-2243-4251-97fe-8201d50c3256",
                    "name": "Délégué de circonscription délégué",
                    "zones": [
                        {
                            "uuid": "e3efac36-906e-11eb-a875-0242ac150002",
                            "code": "CIRCO_FDE-06",
                            "name": "Suisse"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["dashboard", "messages", "events", "mobile_app"],
                    "attributes": null
                },
                {
                    "code": "delegated_d2315289-a3fd-419c-a3dd-3e1ff71b754d",
                    "name": "Délégué de circonscription délégué",
                    "zones": [
                        {
                            "uuid": "e3f0bfff-906e-11eb-a875-0242ac150002",
                            "code": "75-2",
                            "name": "Paris (2)"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["dashboard", "contacts", "mobile_app"],
                    "attributes": null
                },
                {
                    "code": "delegated_01ddb89b-25be-4ccb-a90f-8338c42e7e58",
                    "name": "Candidat délégué",
                    "zones": [
                        {
                            "uuid": "e3efe139-906e-11eb-a875-0242ac150002",
                            "code": "11",
                            "name": "Île-de-France"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["dashboard", "contacts", "messages", "events", "mobile_app", "ripostes", "survey"],
                    "attributes": null
                },
                {
                    "code": "delegated_1d29b80c-a308-441c-9d7d-a333c366fdb1",
                    "name": "Président assemblée départementale délégué",
                    "zones": [
                        {
                            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["dashboard", "mobile_app", "elected_representative"],
                    "attributes": null
                },
                {
                    "code": "delegated_ef339f8e-e9d0-4f22-b98f-1a7526246cad",
                    "name": "Président assemblée départementale délégué",
                    "zones": [
                        {
                            "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                            "code": "77",
                            "name": "Seine-et-Marne"
                        },
                        {
                            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        },
                        {
                            "uuid": "e3efef5d-906e-11eb-a875-0242ac150002",
                            "code": "76",
                            "name": "Seine-Maritime"
                        },
                        {
                            "uuid": "e3eff020-906e-11eb-a875-0242ac150002",
                            "code": "59",
                            "name": "Nord"
                        },
                        {
                            "uuid": "e3f01553-906e-11eb-a875-0242ac150002",
                            "code": "13",
                            "name": "Bouches-du-Rhône"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["dashboard", "contacts", "messages", "events", "mobile_app"],
                    "attributes": null
                },
                {
                    "code": "delegated_6d2506a7-bec7-45a1-a5ee-8f8b48daa5ec",
                    "name": "Responsable local délégué",
                    "zones": [
                        {
                            "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["dashboard", "contacts", "mobile_app"],
                    "attributes": null
                },
                {
                    "code": "delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c",
                    "name": "Candidat aux législatives délégué",
                    "zones": [
                        {
                            "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                            "code": "75-1",
                            "name": "Paris (1)"
                        }
                    ],
                    "apps": ["data_corner"],
                    "features": ["dashboard", "contacts", "events", "mobile_app", "news"],
                    "attributes": null
                }
            ]
            """

    Scenario:
        When I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/profile/me/scope/delegated_d2315289-a3fd-419c-a3dd-3e1ff71b754d"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "code": "delegated_d2315289-a3fd-419c-a3dd-3e1ff71b754d",
                "name": "Délégué de circonscription délégué",
                "zones": [
                    {
                        "uuid": "e3f0bfff-906e-11eb-a875-0242ac150002",
                        "code": "75-2",
                        "type": "district",
                        "name": "Paris (2)",
                        "latitude": null,
                        "longitude": null
                    }
                ],
                "apps": ["data_corner"],
                "features": ["dashboard", "contacts", "mobile_app"],
                "attributes": null,
                "delegated_access": {
                    "delegator": {
                        "uuid": "160cdf45-80c4-4663-aa21-0ae23091a381",
                        "first_name": "Député",
                        "last_name": "PARIS II"
                    },
                    "type": "deputy",
                    "role": "Collaborateur parlementaire"
                }
            }
            """

    Scenario:
        When I am logged with "senateur@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/profile/me/scope/delegated_08f40730-d807-4975-8773-69d8fae1da74"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "code": "delegated_08f40730-d807-4975-8773-69d8fae1da74",
                "name": "Président assemblée départementale délégué",
                "zones": [
                    {
                        "uuid": "e3efe5c5-906e-11eb-a875-0242ac150002",
                        "type": "department",
                        "code": "77",
                        "name": "Seine-et-Marne",
                        "latitude": null,
                        "longitude": null
                    },
                    {
                        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                        "type": "department",
                        "code": "92",
                        "name": "Hauts-de-Seine",
                        "latitude": null,
                        "longitude": null
                    },
                    {
                        "uuid": "e3efef5d-906e-11eb-a875-0242ac150002",
                        "type": "department",
                        "code": "76",
                        "name": "Seine-Maritime",
                        "latitude": null,
                        "longitude": null
                    },
                    {
                        "uuid": "e3eff020-906e-11eb-a875-0242ac150002",
                        "type": "department",
                        "code": "59",
                        "name": "Nord",
                        "latitude": null,
                        "longitude": null
                    },
                    {
                        "uuid": "e3f01553-906e-11eb-a875-0242ac150002",
                        "type": "department",
                        "code": "13",
                        "name": "Bouches-du-Rhône",
                        "latitude": null,
                        "longitude": null
                    }
                ],
                "apps": ["data_corner"],
                "features": [
                    "dashboard",
                    "contacts",
                    "contacts_export",
                    "messages",
                    "events",
                    "my_team",
                    "mobile_app",
                    "news",
                    "elections",
                    "ripostes",
                    "pap",
                    "pap_v2",
                    "team",
                    "phoning_campaign",
                    "survey",
                    "department_site",
                    "elected_representative",
                    "adherent_formations",
                    "committee",
                    "general_meeting_reports",
                    "documents",
                    "designation",
                    "statutory_message",
                    "procurations",
                    "actions",
                    "featurebase",
                    "circonscriptions",
                    "referrals"
                ],
                "delegated_access": {
                    "delegator": {
                        "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                        "first_name": "Referent",
                        "last_name": "Referent"
                    },
                    "role": "Collaborateur parlementaire",
                    "type": "president_departmental_assembly"
                },
                "attributes": null
            }
            """

    Scenario:
        Given I am logged with "adherent-male-55@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/profile/me/scope/animator"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "code": "animator",
                "name": "Responsable comité local",
                "zones": [],
                "apps": ["data_corner"],
                "features": [
                    "dashboard",
                    "contacts",
                    "contacts_export",
                    "messages",
                    "messages_vox",
                    "events",
                    "my_team",
                    "mobile_app",
                    "news",
                    "elections",
                    "ripostes",
                    "pap",
                    "pap_v2",
                    "team",
                    "phoning_campaign",
                    "survey",
                    "department_site",
                    "elected_representative",
                    "adherent_formations",
                    "committee",
                    "general_meeting_reports",
                    "documents",
                    "designation",
                    "statutory_message",
                    "procurations",
                    "actions",
                    "featurebase",
                    "circonscriptions",
                    "referrals"
                ],
                "attributes": {
                    "committees": [{ "name": "Comité des 3 communes", "uuid": "@uuid@" }],
                    "dpt": "92"
                },
                "delegated_access": null
            }
            """

    Scenario:
        When I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/profile/me/scope/test"
        Then the response status code should be 403
