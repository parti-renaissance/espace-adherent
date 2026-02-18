@api
@renaissance
Feature:
    In order to get adherents information
    As a referent
    I should be able to access adherents API data

    Scenario: As a non logged-in user I can not set a nickname
        Given I send a "PUT" request to "/api/adherents/me/anonymize"
        Then the response status code should be 401

    Scenario: As a logged-in user I can not set a nickname of another person
        Given I am logged as "jacques.picard@en-marche.fr"
        When I send a "PUT" request to "/api/adherents/me/anonymize" with body:
            """
            {}
            """
        Then the response status code should be 400
        And the JSON should be equal to:
            """
            "Property \"nickname\" is required."
            """

    Scenario: As a logged-in user I can not set a nickname that used by another person
        Given I am logged as "jacques.picard@en-marche.fr"
        When I send a "PUT" request to "/api/adherents/me/anonymize" with body:
            """
            {
                "nickname": "pont"
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
                        "propertyPath": "nickname",
                        "message": "Cette valeur est déjà utilisée."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I cannot set my nickname if it's too long
        Given I am logged as "jacques.picard@en-marche.fr"
        When I send a "PUT" request to "/api/adherents/me/anonymize" with body:
            """
            {
                "nickname": "ilesttroplongmonnouveaunickname"
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
                        "propertyPath": "nickname",
                        "message": "Vous devez saisir au maximum 25 caractères."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I cannot set my nickname if it contains not authorised caracters
        Given I am logged as "jacques.picard@en-marche.fr"
        When I send a "PUT" request to "/api/adherents/me/anonymize" with body:
            """
            {
                "nickname": "La République En Marche !"
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
                        "propertyPath": "nickname",
                        "message": "La syntaxe est incorrecte, le pseudo ne peut contenir que des chiffres, lettres, et les caractères _ et -"
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can set my nickname but not use it
        Given I am logged as "jacques.picard@en-marche.fr"
        When I send a "PUT" request to "/api/adherents/me/anonymize" with body:
            """
            {
                "nickname": "new nickname"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON

    Scenario: As a logged-in user I can set my nickname and use it
        Given I am logged as "jacques.picard@en-marche.fr"
        When I send a "PUT" request to "/api/adherents/me/anonymize" with body:
            """
            {
                "nickname": "ne-w nick_name",
                "use_nickname": true
            }
            """
        Then the response status code should be 200
        And the response should be in JSON

    Scenario Outline: As a user with (delegated) referent role I can get adherents of my zones
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 3,
                    "items_per_page": 25,
                    "count": 3,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "public_id": null,
                        "adherent_uuid": "@uuid@",
                        "email": "francis.brioul@yahoo.com",
                        "address": "2 avenue Jean Jaurès",
                        "postal_code": "77000",
                        "city": "Melun",
                        "country": "FR",
                        "gender": "male",
                        "first_name": "Francis",
                        "last_name": "Brioul",
                        "image_url": null,
                        "birthdate": "1962-01-07T00:00:00+01:00",
                        "age": @integer@,
                        "agora": null,
                        "agora_uuid": null,
                        "phone": null,
                        "nationality": null,
                        "tags": [
                            {
                                "type": "role",
                                "label": "Président d'assemblée départementale délégué",
                                "tooltip": "Responsable communication"
                            }
                        ],
                        "created_at": "2017-08-12T16:12:13+02:00",
                        "interests": [],
                        "first_membership_donation": null,
                        "last_membership_donation": null,
                        "committee": null,
                        "committee_uuid": null,
                        "mandates": [],
                        "declared_mandates": [],
                        "cotisation_dates": [],
                        "campus_registered_at": null,
                        "zones": [
                            {
                                "uuid": "e3f0cc51-906e-11eb-a875-0242ac150002",
                                "type": "district",
                                "code": "77-1",
                                "name": "Seine-et-Marne (1)"
                            },
                            {
                                "uuid": "e3f2a958-906e-11eb-a875-0242ac150002",
                                "type": "city",
                                "code": "77288",
                                "name": "Melun"
                            }
                        ],
                        "certified": false,
                        "city_code": "77288",
                        "sms_subscription": false,
                        "email_subscription": false,
                        "available_for_resubscribe_email": false
                    },
                    {
                        "public_id": null,
                        "adherent_uuid": "@uuid@",
                        "email": "gisele-berthoux@caramail.com",
                        "address": "47 rue Martre",
                        "postal_code": "92110",
                        "city": "Clichy",
                        "country": "FR",
                        "gender": "female",
                        "first_name": "Gisele",
                        "last_name": "Berthoux",
                        "image_url": null,
                        "birthdate": "1983-12-24T00:00:00+01:00",
                        "age": "@number@",
                        "agora": "Première Agora",
                        "agora_uuid": "82ad6422-cb82-4c04-b478-bfb421c740e0",
                        "phone": "+33 6 66 66 66 66",
                        "nationality": "FR",
                        "tags": [
                            {
                                "code": "adherent:a_jour_2026:recotisation",
                                "label": "Adhérent à jour 2026",
                                "type": "adherent"
                            },
                            {
                                "type": "role",
                                "label": "Déléguée de circonscription déléguée",
                                "tooltip": "Responsable mobilisation"
                            },
                            {
                                "type": "role",
                                "label": "Sénatrice déléguée",
                                "tooltip": "Responsable mobilisation"
                            },
                            {
                                "type": "role",
                                "label": "Déléguée de circonscription déléguée",
                                "tooltip": "Responsable mobilisation"
                            },
                            {
                                "type": "role",
                                "label": "Candidate déléguée",
                                "tooltip": "Candidat délégué"
                            },
                            {
                                "type": "role",
                                "label": "Présidente d'assemblée départementale déléguée",
                                "tooltip": "Responsable élus délégué #1"
                            },
                            {
                                "type": "role",
                                "label": "Présidente d'assemblée départementale déléguée",
                                "tooltip": "Responsable communication"
                            },
                            {
                                "type": "role",
                                "label": "Responsable locale déléguée",
                                "tooltip": "Responsable logistique"
                            },
                            {
                                "type": "role",
                                "label": "Candidate aux législatives déléguée",
                                "tooltip": "Responsable communication"
                            },
                            {
                                "type": "mandate",
                                "label": "Conseiller municipal"
                            }
                        ],
                        "created_at": "2017-06-02T15:34:12+02:00",
                        "interests": [],
                        "first_membership_donation": null,
                        "last_membership_donation": null,
                        "committee": "Second Comité des 3 communes",
                        "committee_uuid": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3",
                        "mandates": ["conseiller_municipal"],
                        "declared_mandates": ["conseiller_municipal"],
                        "cotisation_dates": [],
                        "campus_registered_at": null,
                        "zones": [
                            {
                                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                                "type": "department",
                                "code": "92",
                                "name": "Hauts-de-Seine"
                            },
                            {
                                "uuid": "e3f2c4a0-906e-11eb-a875-0242ac150002",
                                "type": "city",
                                "code": "92024",
                                "name": "Clichy"
                            }
                        ],
                        "certified": true,
                        "city_code": "92024",
                        "sms_subscription": true,
                        "email_subscription": true,
                        "available_for_resubscribe_email": false
                    },
                    {
                        "public_id": null,
                        "adherent_uuid": "@uuid@",
                        "email": "je-mengage-user-1@en-marche-dev.fr",
                        "address": "2 avenue Jean Jaurès",
                        "postal_code": "77000",
                        "city": "Melun",
                        "country": "FR",
                        "gender": "male",
                        "first_name": "Jules",
                        "last_name": "Fullstack",
                        "image_url": null,
                        "birthdate": "1942-01-10T00:00:00+02:00",
                        "age": "@number@",
                        "agora": null,
                        "agora_uuid": null,
                        "phone": null,
                        "nationality": null,
                        "tags": [
                            {
                                "type": "role",
                                "label": "Responsable local",
                                "tooltip": null
                            },
                            {
                                "type": "declared_mandate",
                                "label": "Député européen"
                            },
                            {
                                "type": "declared_mandate",
                                "label": "Conseiller municipal"
                            }
                        ],
                        "created_at": "2017-06-02T15:34:12+02:00",
                        "interests": [],
                        "first_membership_donation": null,
                        "last_membership_donation": null,
                        "committee": null,
                        "committee_uuid": null,
                        "mandates": [],
                        "declared_mandates": ["depute_europeen", "conseiller_municipal"],
                        "cotisation_dates": [],
                        "campus_registered_at": null,
                        "zones": [
                            {
                                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                                "type": "department",
                                "code": "92",
                                "name": "Hauts-de-Seine"
                            },
                            {
                                "uuid": "e3eff020-906e-11eb-a875-0242ac150002",
                                "type": "department",
                                "code": "59",
                                "name": "Nord"
                            }
                        ],
                        "certified": false,
                        "city_code": null,
                        "sms_subscription": false,
                        "email_subscription": false,
                        "available_for_resubscribe_email": false
                    }
                ]
            }
            """
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>&firstName=Gisele&lastName=Berthoux&gender=female&registered%5Bstart%5D=2016-01-01&registered%5Bend%5D=2042-01-01&age%5Bmin%5D=18&age%5Bmax%5D=100"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 1,
                    "items_per_page": 25,
                    "count": 1,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "public_id": null,
                        "adherent_uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                        "email": "gisele-berthoux@caramail.com",
                        "address": "47 rue Martre",
                        "postal_code": "92110",
                        "city": "Clichy",
                        "country": "FR",
                        "gender": "female",
                        "first_name": "Gisele",
                        "last_name": "Berthoux",
                        "image_url": null,
                        "birthdate": "1983-12-24T00:00:00+01:00",
                        "age": "@number@",
                        "agora": "Première Agora",
                        "agora_uuid": "82ad6422-cb82-4c04-b478-bfb421c740e0",
                        "phone": "+33 6 66 66 66 66",
                        "nationality": "FR",
                        "tags": [
                            {
                                "code": "adherent:a_jour_2026:recotisation",
                                "label": "Adhérent à jour 2026",
                                "type": "adherent"
                            },
                            {
                                "type": "role",
                                "label": "Déléguée de circonscription déléguée",
                                "tooltip": "Responsable mobilisation"
                            },
                            {
                                "type": "role",
                                "label": "Sénatrice déléguée",
                                "tooltip": "Responsable mobilisation"
                            },
                            {
                                "type": "role",
                                "label": "Déléguée de circonscription déléguée",
                                "tooltip": "Responsable mobilisation"
                            },
                            {
                                "type": "role",
                                "label": "Candidate déléguée",
                                "tooltip": "Candidat délégué"
                            },
                            {
                                "type": "role",
                                "label": "Présidente d'assemblée départementale déléguée",
                                "tooltip": "Responsable élus délégué #1"
                            },
                            {
                                "type": "role",
                                "label": "Présidente d'assemblée départementale déléguée",
                                "tooltip": "Responsable communication"
                            },
                            {
                                "type": "role",
                                "label": "Responsable locale déléguée",
                                "tooltip": "Responsable logistique"
                            },
                            {
                                "type": "role",
                                "label": "Candidate aux législatives déléguée",
                                "tooltip": "Responsable communication"
                            },
                            {
                                "type": "mandate",
                                "label": "Conseiller municipal"
                            }
                        ],
                        "created_at": "2017-06-02T15:34:12+02:00",
                        "interests": [],
                        "first_membership_donation": null,
                        "last_membership_donation": null,
                        "committee": "Second Comité des 3 communes",
                        "committee_uuid": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3",
                        "mandates": ["conseiller_municipal"],
                        "declared_mandates": ["conseiller_municipal"],
                        "cotisation_dates": [],
                        "campus_registered_at": null,
                        "zones": [
                            {
                                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                                "type": "department",
                                "code": "92",
                                "name": "Hauts-de-Seine"
                            },
                            {
                                "uuid": "e3f2c4a0-906e-11eb-a875-0242ac150002",
                                "type": "city",
                                "code": "92024",
                                "name": "Clichy"
                            }
                        ],
                        "certified": true,
                        "city_code": "92024",
                        "sms_subscription": true,
                        "email_subscription": true,
                        "available_for_resubscribe_email": false
                    }
                ]
            }
            """
        When I send a "GET" request to "/api/v3/adherents/a9fc8d48-6f57-4d89-ae73-50b3f9b586f4?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "public_id": null,
                "adherent_uuid": "a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                "email": "francis.brioul@yahoo.com",
                "address": "2 avenue Jean Jaurès",
                "postal_code": "77000",
                "city": "Melun",
                "country": "FR",
                "gender": "male",
                "first_name": "Francis",
                "last_name": "Brioul",
                "image_url": null,
                "birthdate": "1962-01-07T00:00:00+01:00",
                "age": @integer@,
                "agora": null,
                "agora_uuid": null,
                "phone": null,
                "nationality": null,
                "tags": [
                    {
                        "type": "role",
                        "label": "Président d'assemblée départementale délégué",
                        "tooltip": "Responsable communication"
                    }
                ],
                "created_at": "2017-08-12T16:12:13+02:00",
                "interests": [],
                "first_membership_donation": null,
                "last_membership_donation": null,
                "committee": null,
                "committee_uuid": null,
                "mandates": [],
                "declared_mandates": [],
                "cotisation_dates": [],
                "campus_registered_at": null,
                "zones": [
                    {
                        "uuid": "e3f0cc51-906e-11eb-a875-0242ac150002",
                        "type": "district",
                        "code": "77-1",
                        "name": "Seine-et-Marne (1)"
                    },
                    {
                        "uuid": "e3f2a958-906e-11eb-a875-0242ac150002",
                        "type": "city",
                        "code": "77288",
                        "name": "Melun"
                    }
                ],
                "certified": false,
                "sms_subscription": false,
                "email_subscription": false,
                "available_for_resubscribe_email": false
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a user with correspondent role I can get adherents of my zones
        Given I am logged with "je-mengage-user-1@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents?scope=correspondent"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 2,
                    "items_per_page": 25,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "public_id": null,
                        "adherent_uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                        "email": "gisele-berthoux@caramail.com",
                        "address": "47 rue Martre",
                        "postal_code": "92110",
                        "city": "Clichy",
                        "country": "FR",
                        "gender": "female",
                        "first_name": "Gisele",
                        "last_name": "Berthoux",
                        "image_url": null,
                        "birthdate": "1983-12-24T00:00:00+01:00",
                        "age": "@number@",
                        "agora": "Première Agora",
                        "agora_uuid": "82ad6422-cb82-4c04-b478-bfb421c740e0",
                        "phone": "+33 6 66 66 66 66",
                        "nationality": "FR",
                        "tags": [
                            {
                                "code": "adherent:a_jour_2026:recotisation",
                                "label": "Adhérent à jour 2026",
                                "type": "adherent"
                            },
                            {
                                "type": "role",
                                "label": "Déléguée de circonscription déléguée",
                                "tooltip": "Responsable mobilisation"
                            },
                            {
                                "type": "role",
                                "label": "@string@.oneOf(
                                    contains('Sénateur délégué'),
                                    contains('Sénatrice déléguée')
                                )",
                                "tooltip": "Responsable mobilisation"
                            },
                            {
                                "type": "role",
                                "label": "Déléguée de circonscription déléguée",
                                "tooltip": "Responsable mobilisation"
                            },
                            {
                                "type": "role",
                                "label": "@string@.oneOf(
                                    contains('Candidat délégué'),
                                    contains('Candidate déléguée')
                                )",
                                "tooltip": "Candidat délégué"
                            },
                            {
                                "type": "role",
                                "label": "Présidente d'assemblée départementale déléguée",
                                "tooltip": "Responsable élus délégué #1"
                            },
                            {
                                "type": "role",
                                "label": "Présidente d'assemblée départementale déléguée",
                                "tooltip": "Responsable communication"
                            },
                            {
                                "type": "role",
                                "label": "Responsable locale déléguée",
                                "tooltip": "Responsable logistique"
                            },
                            {
                                "type": "role",
                                "label": "Candidate aux législatives déléguée",
                                "tooltip": "Responsable communication"
                            },
                            {
                                "type": "mandate",
                                "label": "Conseiller municipal"
                            }
                        ],
                        "created_at": "2017-06-02T15:34:12+02:00",
                        "interests": [],
                        "first_membership_donation": null,
                        "last_membership_donation": null,
                        "committee": "Second Comité des 3 communes",
                        "committee_uuid": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3",
                        "mandates": ["conseiller_municipal"],
                        "declared_mandates": ["conseiller_municipal"],
                        "cotisation_dates": [],
                        "campus_registered_at": null,
                        "zones": [
                            {
                                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                                "type": "department",
                                "code": "92",
                                "name": "Hauts-de-Seine"
                            },
                            {
                                "uuid": "e3f2c4a0-906e-11eb-a875-0242ac150002",
                                "type": "city",
                                "code": "92024",
                                "name": "Clichy"
                            }
                        ],
                        "certified": true,
                        "city_code": "92024",
                        "sms_subscription": true,
                        "email_subscription": true,
                        "available_for_resubscribe_email": false
                    },
                    {
                        "public_id": null,
                        "adherent_uuid": "@uuid@",
                        "email": "je-mengage-user-1@en-marche-dev.fr",
                        "address": "2 avenue Jean Jaurès",
                        "postal_code": "77000",
                        "city": "Melun",
                        "country": "FR",
                        "gender": "male",
                        "first_name": "Jules",
                        "last_name": "Fullstack",
                        "image_url": null,
                        "birthdate": "1942-01-10T00:00:00+02:00",
                        "age": "@number@",
                        "agora": null,
                        "agora_uuid": null,
                        "phone": null,
                        "nationality": null,
                        "tags": [
                            {
                                "type": "role",
                                "label": "Responsable local",
                                "tooltip": null
                            },
                            {
                                "type": "declared_mandate",
                                "label": "Député européen"
                            },
                            {
                                "type": "declared_mandate",
                                "label": "Conseiller municipal"
                            }
                        ],
                        "created_at": "2017-06-02T15:34:12+02:00",
                        "interests": [],
                        "first_membership_donation": null,
                        "last_membership_donation": null,
                        "committee": null,
                        "committee_uuid": null,
                        "mandates": [],
                        "declared_mandates": ["depute_europeen", "conseiller_municipal"],
                        "cotisation_dates": [],
                        "campus_registered_at": null,
                        "zones": [
                            {
                                "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                                "type": "department",
                                "code": "92",
                                "name": "Hauts-de-Seine"
                            },
                            {
                                "uuid": "e3eff020-906e-11eb-a875-0242ac150002",
                                "type": "department",
                                "code": "59",
                                "name": "Nord"
                            }
                        ],
                        "certified": false,
                        "city_code": null,
                        "sms_subscription": false,
                        "email_subscription": false,
                        "available_for_resubscribe_email": false
                    }
                ]
            }
            """
        When I send a "GET" request to "/api/v3/adherents?scope=correspondent&onlyJeMengageUsers=1"
        Then the response status code should be 200
        And the JSON nodes should match:
            | metadata.total_items | 1 |

    Scenario Outline: As a user with (delegated) legislative candidate role I can get filters list to filter adherents
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/filters?scope=<scope>&feature=contacts"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "label": "",
                    "color": "",
                    "filters": [
                        {
                            "code": "search_term",
                            "label": "Recherche",
                            "options": {
                                "favorite": true
                            },
                            "type": "text"
                        }
                    ]
                },
                {
                    "label": "Informations personnelles",
                    "color": "#0E7490",
                    "filters": [
                        {
                            "code": "gender",
                            "label": "Civilité",
                            "options": {
                                "choices": {
                                    "female": "Madame",
                                    "male": "Monsieur"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "age",
                            "label": "Âge",
                            "options": {
                                "suffix": "ans",
                                "first": {
                                    "label": "Âgé d'au moins",
                                    "min": 1,
                                    "max": 200
                                },
                                "second": {
                                    "label": "Âgé de maximum",
                                    "min": 1,
                                    "max": 200
                                }
                            },
                            "type": "integer_interval"
                        },
                        {
                            "code": "email_subscription",
                            "label": "Abonné email",
                            "options": {
                                "choices": ["Non", "Oui"]
                            },
                            "type": "select"
                        },
                        {
                            "code": "first_name",
                            "label": "Prénom",
                            "options": null,
                            "type": "text"
                        },
                        {
                            "code": "last_name",
                            "label": "Nom",
                            "options": null,
                            "type": "text"
                        },
                        {
                            "code": "sms_subscription",
                            "label": "Abonné SMS",
                            "options": {
                                "choices": ["Non", "Oui"]
                            },
                            "type": "select"
                        },
                        {
                            "code": "zones",
                            "label": "Zone géographique",
                            "options": {
                                "url": "/api/v3/zone/autocomplete",
                                "query_param": "q",
                                "value_param": "uuid",
                                "label_param": "name",
                                "multiple": true,
                                "help": "<strong>Toutes les zones incluses dans votre zone de gestion sont filtrables.</strong> Exemple : Arrondissement, Canton, Ville",
                                "required": false
                            },
                            "type": "zone_autocomplete"
                        }
                    ]
                },
                {
                    "label": "Militant",
                    "color": "#0F766E",
                    "filters": [
                        {
                            "code": "adherent_tags",
                            "label": "Labels adhérent",
                            "options": {
                                "placeholder": "Tous mes militants",
                                "advanced": false,
                                "favorite": true,
                                "required": false,
                                "choices": {
                                    "adherent": "Adhérent",
                                    "adherent:a_jour_2026": "Adhérent - À jour 2026",
                                    "adherent:a_jour_2026:primo": "Adhérent - À jour 2026 - Primo-adhérent",
                                    "adherent:a_jour_2026:recotisation": "Adhérent - À jour 2026 - Recotisation",
                                    "adherent:a_jour_2026:elu_a_jour": "Adhérent - À jour 2026 - Élu à jour",
                                    "adherent:plus_a_jour": "Adhérent - Plus à jour",
                                    "adherent:plus_a_jour:annee_2025": "Adhérent - Plus à jour - À jour 2025",
                                    "adherent:plus_a_jour:annee_2024": "Adhérent - Plus à jour - À jour 2024",
                                    "adherent:plus_a_jour:annee_2023": "Adhérent - Plus à jour - À jour 2023",
                                    "adherent:plus_a_jour:annee_2022": "Adhérent - Plus à jour - À jour 2022",
                                    "sympathisant": "Sympathisant",
                                    "sympathisant:adhesion_incomplete": "Sympathisant - Adhésion incomplète",
                                    "sympathisant:compte_em": "Sympathisant - Ancien compte En Marche",
                                    "sympathisant:ensemble2024": "Sympathisant - Ensemble 2024",
                                    "sympathisant:compte_avecvous_jemengage": "Sympathisant - Anciens comptes Je m'engage et Avec vous",
                                    "sympathisant:autre_parti": "Sympathisant - Adhérent d'un autre parti",
                                    "sympathisant:besoin_d_europe": "Sympathisant - Besoin d'Europe"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "static_tags",
                            "label": "Labels divers",
                            "options": {
                                "advanced": false,
                                "favorite": true,
                                "required": false,
                                "choices": {
                                    "national_event:campus": "Campus",
                                    "national_event:meeting-nrp": "Meeting NRP",
                                    "national_event:event-national-1": "Event national 1",
                                    "national_event:event-national-2": "Event national 2",
                                    "national_event:present:campus": "Présent Campus",
                                    "national_event:present:event-passe": "Présent Event passé"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "first_membership",
                            "label": "Première cotisation",
                            "options": null,
                            "type": "date_interval"
                        },
                        {
                            "code": "last_membership",
                            "label": "Dernière cotisation",
                            "options": null,
                            "type": "date_interval"
                        },
                        {
                            "code": "registered",
                            "label": "Création du compte",
                            "options": null,
                            "type": "date_interval"
                        }
                    ]
                },
                {
                    "label": "Élu",
                    "color": "#2563EB",
                    "filters": [
                        {
                            "code": "declared_mandates",
                            "label": "Déclaration de mandat",
                            "options": {
                                "advanced": false,
                                "choices": {
                                    "depute_europeen": "Député européen",
                                    "senateur": "Sénateur",
                                    "depute": "Député",
                                    "president_conseil_regional": "Président du Conseil régional",
                                    "conseiller_regional": "Conseiller régional",
                                    "president_conseil_departemental": "Président du Conseil départemental",
                                    "conseiller_departemental": "Conseiller départemental",
                                    "conseiller_territorial": "Conseiller territorial",
                                    "president_conseil_communautaire": "Président du Conseil communautaire",
                                    "conseiller_communautaire": "Conseiller communautaire",
                                    "maire": "Maire",
                                    "conseiller_municipal": "Conseiller municipal",
                                    "conseiller_arrondissement": "Conseiller d'arrondissement",
                                    "membre_assemblee_fde": "Membre de l'Assemblée des Français de l'étranger",
                                    "ministre": "Ministre",
                                    "conseiller_fde": "Conseiller FDE",
                                    "delegue_consulaire": "Délégué consulaire"
                                },
                                "multiple": true
                            },
                            "type": "select"
                        },
                        {
                            "code": "elect_tags",
                            "label": "Labels élu",
                            "options": {
                                "advanced": false,
                                "favorite": true,
                                "required": false,
                                "choices": {
                                    "elu": "Élu",
                                    "elu:attente_declaration": "Élu - En attente de déclaration",
                                    "elu:cotisation_ok": "Élu - À jour de cotisation",
                                    "elu:cotisation_ok:exempte": "Élu - À jour de cotisation - Exempté de cotisation",
                                    "elu:cotisation_ok:non_soumis": "Élu - À jour de cotisation - Non soumis à cotisation",
                                    "elu:cotisation_ok:soumis": "Élu - À jour de cotisation - Soumis à cotisation",
                                    "elu:cotisation_nok": "Élu - Non à jour de cotisation",
                                    "elu:exempte_et_adherent_cotisation_nok": "Élu - Exempté mais pas à jour de cotisation adhérent"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "mandates",
                            "label": "Type de mandat",
                            "options": {
                                "advanced": false,
                                "choices": {
                                    "depute_europeen": "Député européen",
                                    "senateur": "Sénateur",
                                    "depute": "Député",
                                    "president_conseil_regional": "Président du Conseil régional",
                                    "conseiller_regional": "Conseiller régional",
                                    "president_conseil_departemental": "Président du Conseil départemental",
                                    "conseiller_departemental": "Conseiller départemental",
                                    "conseiller_territorial": "Conseiller territorial",
                                    "president_conseil_communautaire": "Président du Conseil communautaire",
                                    "conseiller_communautaire": "Conseiller communautaire",
                                    "maire": "Maire",
                                    "conseiller_municipal": "Conseiller municipal",
                                    "conseiller_arrondissement": "Conseiller d'arrondissement",
                                    "membre_assemblee_fde": "Membre de l'Assemblée des Français de l'étranger",
                                    "ministre": "Ministre",
                                    "conseiller_fde": "Conseiller FDE",
                                    "delegue_consulaire": "Délégué consulaire"
                                },
                                "multiple": true
                            },
                            "type": "select"
                        }
                    ]
                }
            ]
            """

        Examples:
            | user                                  | scope                                          |
            | senatorial-candidate@en-marche-dev.fr | legislative_candidate                          |
            | gisele-berthoux@caramail.com          | delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c |

    Scenario Outline: As a user with (delegated) legislative candidate role I can get adherents of my zones
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 2,
                    "items_per_page": 25,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "public_id": null,
                        "adherent_uuid": "918f07e5-676b-49c0-b76d-72ce01cb2404",
                        "email": "deputy@en-marche-dev.fr",
                        "address": "3 Avenue du Général Eisenhower",
                        "postal_code": "75008",
                        "city": "Paris 8ème",
                        "country": "FR",
                        "gender": "male",
                        "first_name": "Député",
                        "last_name": "PARIS I",
                        "image_url": null,
                        "birthdate": "1982-06-02T00:00:00+02:00",
                        "age": "@number@",
                        "agora": null,
                        "agora_uuid": null,
                        "phone": null,
                        "nationality": null,
                        "tags": [
                            {
                                "type": "role",
                                "label": "National communication : Directeur général adjoint",
                                "tooltip": null
                            },
                            {
                                "type": "role",
                                "label": "Délégué de circonscription",
                                "tooltip": null
                            },
                            {
                                "type": "role",
                                "label": "Président d'assemblée départementale délégué",
                                "tooltip": "Responsable communication"
                            }
                        ],
                        "created_at": "2017-06-01T09:26:31+02:00",
                        "interests": ["europe", "numerique"],
                        "first_membership_donation": null,
                        "last_membership_donation": null,
                        "committee": null,
                        "committee_uuid": null,
                        "mandates": [],
                        "declared_mandates": [],
                        "cotisation_dates": ["2022-01-01 12:00:00", "2023-01-01 12:00:00"],
                        "campus_registered_at": null,
                        "zones": [
                            {
                                "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                                "type": "district",
                                "code": "75-1",
                                "name": "Paris (1)"
                            },
                            {
                                "uuid": "e3f2fd15-906e-11eb-a875-0242ac150002",
                                "type": "borough",
                                "code": "75108",
                                "name": "Paris 8ème"
                            }
                        ],
                        "certified": true,
                        "city_code": "75056",
                        "sms_subscription": true,
                        "email_subscription": true,
                        "available_for_resubscribe_email": false
                    },
                    {
                        "public_id": null,
                        "adherent_uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                        "email": "jacques.picard@en-marche.fr",
                        "address": "36 rue de la Paix",
                        "postal_code": "75008",
                        "city": "Paris 8ème",
                        "country": "FR",
                        "gender": "male",
                        "first_name": "Jacques",
                        "last_name": "Picard",
                        "image_url": null,
                        "birthdate": "1953-04-03T00:00:00+01:00",
                        "age": "@number@",
                        "agora": null,
                        "agora_uuid": null,
                        "phone": "+33 1 87 26 42 36",
                        "nationality": "FR",
                        "tags": [
                            {
                                "code": "adherent:a_jour_2026:recotisation",
                                "label": "Adhérent à jour 2026",
                                "type": "adherent"
                            },
                            {
                                "type": "role",
                                "label": "Président d'assemblée départementale délégué",
                                "tooltip": "Responsable mobilisation"
                            }
                        ],
                        "created_at": "2017-01-03T08:47:54+01:00",
                        "interests": ["europe", "numerique", "sante"],
                        "first_membership_donation": null,
                        "last_membership_donation": null,
                        "committee": null,
                        "committee_uuid": null,
                        "mandates": [],
                        "declared_mandates": [],
                        "cotisation_dates": ["2022-02-01 12:00:00", "2023-03-01 12:00:00"],
                        "campus_registered_at": null,
                        "zones": [
                            {
                                "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                                "type": "district",
                                "code": "75-1",
                                "name": "Paris (1)"
                            },
                            {
                                "uuid": "e3f2fd15-906e-11eb-a875-0242ac150002",
                                "type": "borough",
                                "code": "75108",
                                "name": "Paris 8ème"
                            }
                        ],
                        "certified": true,
                        "city_code": "75056",
                        "sms_subscription": true,
                        "email_subscription": true,
                        "available_for_resubscribe_email": false
                    }
                ]
            }
            """
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>&firstName=Jacques&lastName=Picard&gender=male&registered%5Bstart%5D=2017-01-01&registered%5Bend%5D=2022-01-01&age%5Bmin%5D=25&age%5Bmax%5D=90&isCertified=1&emailSubscription=1&smsSubscription=1"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 1,
                    "items_per_page": 25,
                    "count": 1,
                    "current_page": 1,
                    "last_page": 1
                },
                "items": [
                    {
                        "public_id": null,
                        "adherent_uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                        "email": "jacques.picard@en-marche.fr",
                        "address": "36 rue de la Paix",
                        "postal_code": "75008",
                        "city": "Paris 8ème",
                        "country": "FR",
                        "gender": "male",
                        "first_name": "Jacques",
                        "last_name": "Picard",
                        "image_url": null,
                        "birthdate": "1953-04-03T00:00:00+01:00",
                        "age": "@number@",
                        "agora": null,
                        "agora_uuid": null,
                        "phone": "+33 1 87 26 42 36",
                        "nationality": "FR",
                        "tags": [
                            {
                                "code": "adherent:a_jour_2026:recotisation",
                                "label": "Adhérent à jour 2026",
                                "type": "adherent"
                            },
                            {
                                "type": "role",
                                "label": "Président d'assemblée départementale délégué",
                                "tooltip": "Responsable mobilisation"
                            }
                        ],
                        "created_at": "2017-01-03T08:47:54+01:00",
                        "interests": ["europe", "numerique", "sante"],
                        "first_membership_donation": null,
                        "last_membership_donation": null,
                        "committee": null,
                        "committee_uuid": null,
                        "mandates": [],
                        "declared_mandates": [],
                        "cotisation_dates": ["2022-02-01 12:00:00", "2023-03-01 12:00:00"],
                        "campus_registered_at": null,
                        "zones": [
                            {
                                "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
                                "type": "district",
                                "code": "75-1",
                                "name": "Paris (1)"
                            },
                            {
                                "uuid": "e3f2fd15-906e-11eb-a875-0242ac150002",
                                "type": "borough",
                                "code": "75108",
                                "name": "Paris 8ème"
                            }
                        ],
                        "certified": true,
                        "city_code": "75056",
                        "sms_subscription": true,
                        "email_subscription": true,
                        "available_for_resubscribe_email": false
                    }
                ]
            }
            """
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>&onlyJeMengageUsers=1"
        Then the response status code should be 200
        And the JSON nodes should match:
            | metadata.total_items | 0 |
        When I send a "GET" request to "/api/v3/adherents?scope=<scope>&onlyJeMengageUsers=0"
        Then the response status code should be 200
        And the JSON nodes should match:
            | metadata.total_items | 2 |

        Examples:
            | user                                  | scope                                          |
            | senatorial-candidate@en-marche-dev.fr | legislative_candidate                          |
            | gisele-berthoux@caramail.com          | delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c |

    Scenario: I can count all adherent RE in my zone
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/count?scope=president_departmental_assembly"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "adherent": 7,
                "sympathizer": 3
            }
            """
        When I send a "GET" request to "/api/v3/adherents/count?scope=president_departmental_assembly&since=2022"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "adherent": 7,
                "adherent_since": 6,
                "sympathizer": 3
            }
            """
        When I send a "POST" request to "/api/v3/adherents/count?scope=president_departmental_assembly" with body:
            """
            ["e3efe6fd-906e-11eb-a875-0242ac150002"]
            """
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "adherent": 3,
                "sympathizer": 1
            }
            """
