@api
@renaissance
Feature:
    In order to see, create, edit and delete adherent messages
    As a logged-in user
    I should be able to access API adherent messages

    Scenario Outline: As a logged-in (delegated) referent I can retrieve my messages
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherent_messages?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "metadata": {
                    "total_items": 102,
                    "items_per_page": 2,
                    "count": 2,
                    "current_page": 1,
                    "last_page": 51
                },
                "items": [
                    {
                        "uuid": "@uuid@",
                        "label": "@string@",
                        "subject": "@string@",
                        "status": "draft",
                        "recipient_count": 0,
                        "source": "cadre",
                        "synchronized": false,
                        "from_name": "Referent Referent | Renaissance",
                        "created_at": "@string@.isDateTime()",
                        "sent_at": null,
                        "author": {
                            "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                            "first_name": "Referent",
                            "last_name": "Referent"
                        },
                        "sender": {
                            "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                            "first_name": "Referent",
                            "last_name": "Referent",
                            "instance": null,
                            "scope": null,
                            "role": null,
                            "zone": null,
                            "theme": null
                        },
                        "preview_link": null,
                        "statistics": {
                            "click_rate": 0,
                            "clicks": 0,
                            "open_rate": 0,
                            "opens": 0,
                            "sent": 0,
                            "unsubscribe": 0,
                            "unsubscribe_rate": 0
                        }
                    },
                    {
                        "uuid": "@uuid@",
                        "label": "@string@",
                        "subject": "@string@",
                        "status": "sent",
                        "recipient_count": 0,
                        "source": "cadre",
                        "synchronized": false,
                        "preview_link": null,
                        "from_name": "Referent Referent | Renaissance",
                        "created_at": "@string@.isDateTime()",
                        "sent_at": "@string@.isDateTime()",
                        "author": {
                            "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                            "first_name": "Referent",
                            "last_name": "Referent"
                        },
                        "sender": {
                            "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                            "first_name": "Referent",
                            "last_name": "Referent",
                            "instance": null,
                            "scope": null,
                            "role": null,
                            "zone": null,
                            "theme": null
                        },
                        "statistics": {
                            "click_rate": 0,
                            "clicks": 0,
                            "open_rate": 0,
                            "opens": 0,
                            "sent": 0,
                            "unsubscribe": 0,
                            "unsubscribe_rate": 0
                        }
                    }
                ]
            }
            """

        Examples:
            | user                      | scope                           |
            | referent@en-marche-dev.fr | president_departmental_assembly |

    # senateur@en-marche-dev.fr has a delegated access from referent@en-marche-dev.fr and should see the same messages
    Scenario Outline: As a (delegated) referent I can get a content of a message
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/content?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "969b1f08-53ec-4a7d-8d6e-7654a001b13f",
                "subject": "@string@",
                "content": "<html><head><title>@string@</body></html>",
                "json_content": null
            }
            """

        Examples:
            | user                      | scope                           |
            | referent@en-marche-dev.fr | president_departmental_assembly |

    Scenario: As a DC referent I cannot delete a message already sent
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "DELETE" request to "/api/v3/adherent_messages/65f6cdbf-0707-4940-86d8-cc1755aab17e?scope=president_departmental_assembly"
        Then the response status code should be 403

    Scenario Outline: As a (delegated) referent I can delete a draft message
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "DELETE" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f?scope=<scope>"
        Then the response status code should be 204

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a (delegated) Correspondent I can create a message
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/adherent_messages?scope=<scope>" with body:
            """
            {
                "sender": "46ab0600-b5a0-59fc-83a7-cc23ca459ca0",
                "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
                "subject": "L'objet de l'email",
                "content": "<table>...</table>",
                "json_content": "{\"foo\": \"bar\", \"items\": [1, 2, true, \"hello world\"]}"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "@uuid@",
                "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
                "subject": "L'objet de l'email",
                "status": "draft",
                "recipient_count": 0,
                "preview_link": null,
                "source": "vox",
                "synchronized": false,
                "author": {
                    "uuid": "@uuid@",
                    "first_name": "@string@",
                    "last_name": "@string@",
                    "image_url": null
                },
                "sender": {
                    "uuid": "@uuid@",
                    "first_name": "Michel",
                    "last_name": "VASSEUR",
                    "image_url": null,
                    "instance": "Assemblée départementale",
                    "scope": null,
                    "role": "Responsable mobilisation",
                    "zone": "Hauts-de-Seine",
                    "theme": {
                        "active": "#1C5CD8",
                        "hover": "#2F6FE0",
                        "primary": "#3A7DFF",
                        "soft": "#E8F0FF"
                    }
                },
                "updated_at": "@string@.isDateTime()"
            }
            """

        Examples:
            | user                            | scope                                          |
            | president-ad@renaissance-dev.fr | president_departmental_assembly                |
            | kiroule.p@blabla.tld            | delegated_4e1eddaf-00e3-4670-aa11-24420da834c4 |

    Scenario: As a delegated referent I can create a message
        Given I am logged with "senateur@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/adherent_messages?scope=delegated_08f40730-d807-4975-8773-69d8fae1da74" with body:
            """
            {
                "sender": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                "label": "Message d'un référent délégué",
                "subject": "L'objet de l'email",
                "content": "<table>...</table>",
                "json_content": "{\"foo\": \"bar\", \"items\": [1, 2, true, \"hello world\"]}"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "@uuid@",
                "label": "Message d'un référent délégué",
                "subject": "L'objet de l'email",
                "status": "draft",
                "recipient_count": 0,
                "preview_link": null,
                "source": "vox",
                "synchronized": false,
                "author": {
                    "uuid": "@uuid@",
                    "first_name": "Bob",
                    "last_name": "Senateur (59)",
                    "image_url": null
                },
                "sender": {
                    "uuid": "@uuid@",
                    "first_name": "Referent",
                    "last_name": "Referent",
                    "image_url": null,
                    "instance": "Assemblée départementale",
                    "scope": null,
                    "role": "Président",
                    "zone": "Seine-et-Marne, Hauts-de-Seine, Seine-Maritime, Nord, Bouches-du-Rhône",
                    "theme": {
                        "active": "#1C5CD8",
                        "hover": "#2F6FE0",
                        "primary": "#3A7DFF",
                        "soft": "#E8F0FF"
                    }
                },
                "updated_at": "@string@.isDateTime()"
            }
            """

    Scenario Outline: As a (delegated) referent I can update a draft message
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "PUT" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f?scope=<scope>" with body:
            """
            {
                "label": "Mon nouveau titre",
                "subject": "Mon nouveau objet de l'email",
                "content": "<table>nouveau</table>",
                "json_content": "{\"items\": [\"nouveau\"]}"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "969b1f08-53ec-4a7d-8d6e-7654a001b13f",
                "label": "Mon nouveau titre",
                "subject": "Mon nouveau objet de l'email",
                "status": "draft",
                "recipient_count": 0,
                "preview_link": null,
                "source": "vox",
                "synchronized": false,
                "author": {
                    "uuid": "@uuid@",
                    "first_name": "Referent",
                    "last_name": "Referent",
                    "image_url": null
                },
                "sender": {
                    "uuid": "@uuid@",
                    "first_name": "Referent",
                    "last_name": "Referent",
                    "image_url": null,
                    "instance": null,
                    "scope": null,
                    "role": null,
                    "zone": null,
                    "theme": null
                },
                "updated_at": "@string@.isDateTime()"
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario: As a delegated referent I can access to sending a referent message
        Given I am logged with "senateur@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/adherent_messages/969b1f08-53ec-4a7d-8d6e-7654a001b13f/send?scope=delegated_08f40730-d807-4975-8773-69d8fae1da74"
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "detail" should be equal to "The message is not yet ready to send."

    Scenario: As a regional coordinator I can create a message
        Given I am logged with "coordinateur@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/adherent_messages?scope=regional_coordinator" with body:
            """
            {
                "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
                "subject": "L'objet de l'email",
                "content": "<table>...</table>",
                "json_content": "{\"foo\": \"bar\", \"items\": [1, 2, true, \"hello world\"]}"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "@uuid@",
                "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
                "subject": "L'objet de l'email",
                "status": "draft",
                "recipient_count": 0,
                "preview_link": null,
                "source": "vox",
                "synchronized": false,
                "author": {
                    "uuid": "@uuid@",
                    "first_name": "Coordinateur",
                    "last_name": "Coordinateur",
                    "image_url": null
                },
                "sender": {
                    "uuid": "@uuid@",
                    "first_name": "Coordinateur",
                    "last_name": "Coordinateur",
                    "image_url": null,
                    "instance": "Région",
                    "scope": null,
                    "role": "Coordinateur",
                    "zone": "Provence-Alpes-Côte d'Azur",
                    "theme": {
                        "active": "#1C5CD8",
                        "hover": "#2F6FE0",
                        "primary": "#3A7DFF",
                        "soft": "#E8F0FF"
                    }
                },
                "updated_at": "@string@.isDateTime()"
            }
            """

    Scenario Outline: As a user with (delegated) referent role I can get filters list for message feature
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/filters?scope=<scope>&feature=messages"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "label": "Informations personnelles",
                    "color": "#0E7490",
                    "filters": [
                        {
                            "code": "gender",
                            "label": "Civilité",
                            "options": {
                                "choices": {
                                    "female": "Femme",
                                    "male": "Homme",
                                    "other": "Autre"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "firstName",
                            "label": "Prénom",
                            "options": null,
                            "type": "text"
                        },
                        {
                            "code": "lastName",
                            "label": "Nom",
                            "options": null,
                            "type": "text"
                        },
                        {
                            "code": "age",
                            "label": "Âge",
                            "options": {
                                "first": {
                                    "min": 1,
                                    "max": 200
                                },
                                "second": {
                                    "min": 1,
                                    "max": 200
                                }
                            },
                            "type": "integer_interval"
                        },
                        {
                            "code": "zone",
                            "label": "Zone géographique",
                            "options": {
                                "url": "/api/v3/zone/autocomplete?types%5B0%5D=borough&types%5B1%5D=canton&types%5B2%5D=city&types%5B3%5D=department&types%5B4%5D=region&types%5B5%5D=country&types%5B6%5D=district&types%5B7%5D=foreign_district&types%5B8%5D=custom",
                                "query_param": "q",
                                "value_param": "uuid",
                                "label_param": "name",
                                "multiple": false,
                                "required": true
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
                                "placeholder": "Tous les militants",
                                "advanced": true,
                                "favorite": true,
                                "choices": {
                                    "adherent": "Adhérent",
                                    "adherent:a_jour_2025": "Adhérent - À jour 2025",
                                    "adherent:a_jour_2025:primo": "Adhérent - À jour 2025 - Primo-adhérent",
                                    "adherent:a_jour_2025:recotisation": "Adhérent - À jour 2025 - Recotisation",
                                    "adherent:a_jour_2025:elu_a_jour": "Adhérent - À jour 2025 - Élu à jour",
                                    "adherent:plus_a_jour": "Adhérent - Plus à jour",
                                    "adherent:plus_a_jour:annee_2024": "Adhérent - Plus à jour - À jour 2024",
                                    "adherent:plus_a_jour:annee_2023": "Adhérent - Plus à jour - À jour 2023",
                                    "adherent:plus_a_jour:annee_2022": "Adhérent - Plus à jour - À jour 2022",
                                    "sympathisant": "Sympathisant",
                                    "sympathisant:adhesion_incomplete": "Sympathisant - Adhésion incomplète",
                                    "sympathisant:compte_em": "Sympathisant - Ancien compte En Marche",
                                    "sympathisant:compte_avecvous_jemengage": "Sympathisant - Anciens comptes Je m'engage et Avec vous",
                                    "sympathisant:autre_parti": "Sympathisant - Adhérent d'un autre parti",
                                    "sympathisant:besoin_d_europe": "Sympathisant - Besoin d'Europe",
                                    "sympathisant:ensemble2024": "Sympathisant - Ensemble 2024"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "committee",
                            "label": "Comités",
                            "options": {
                                "choices": {
                                    "5e00c264-1d4b-43b8-862e-29edc38389b3": "Comité des 3 communes",
                                    "508d4ac0-27d6-4635-8953-4cc8600018f9": "En Marche - Comité de Rouen",
                                    "b0cd0e52-a5a4-410b-bba3-37afdd326a0a": "En Marche Dammarie-les-Lys",
                                    "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3": "Second Comité des 3 communes",
                                    "d648d486-fbb3-4394-b4b3-016fac3658af": "Antenne En Marche de Fontainebleau"
                                },
                                "multiple": false,
                                "required": false
                            },
                            "type": "select"
                        },
                        {
                            "code": "isCommitteeMember",
                            "label": "Membre d'un comité",
                            "options": {
                                "choices": ["Non", "Oui"]
                            },
                            "type": "select"
                        },
                        {
                            "code": "donatorStatus",
                            "label": "Donateur",
                            "options": {
                                "choices": {
                                    "donator_n": "Donateur année en cours",
                                    "donator_n-x": "Donateur années passées uniquement",
                                    "not_donator": "Pas encore donateur"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "static_tags",
                            "label": "Labels divers",
                            "options": {
                                "advanced": true,
                                "favorite": true,
                                "choices": {
                                    "national_event:campus": "Campus",
                                    "national_event:event-national-1": "Event National 1",
                                    "national_event:event-national-2": "Event National 2"
                                }
                            },
                            "type": "select"
                        },
                        {
                            "code": "registered",
                            "label": "Inscrit",
                            "options": null,
                            "type": "date_interval"
                        },
                        {
                            "code": "firstMembership",
                            "label": "Première cotisation",
                            "options": null,
                            "type": "date_interval"
                        },
                        {
                            "code": "lastMembership",
                            "label": "Dernière cotisation",
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
                            "code": "declaredMandate",
                            "label": "Déclaration de mandat",
                            "options": {
                                "advanced": true,
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
                                "multiple": false
                            },
                            "type": "select"
                        },
                        {
                            "code": "elect_tags",
                            "label": "Labels élu",
                            "options": {
                                "advanced": true,
                                "favorite": true,
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
                            "code": "mandateType",
                            "label": "Type de mandat",
                            "options": {
                                "advanced": true,
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
                                "multiple": false
                            },
                            "type": "select"
                        }
                    ]
                }
            ]
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: I can retrieve available senders list
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/adherent_messages/available-senders?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "uuid": "@uuid@",
                    "image_url": null,
                    "first_name": "Referent",
                    "last_name": "Referent",
                    "instance": "Assemblée départementale",
                    "role": "Président",
                    "zone": "Seine-et-Marne, Hauts-de-Seine, Seine-Maritime, Nord, Bouches-du-Rhône",
                    "theme": {
                        "primary": "#3A7DFF",
                        "soft": "#E8F0FF",
                        "hover": "#2F6FE0",
                        "active": "#1C5CD8"
                    }
                }
            ]
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: I can retrieve recipients count
        Given I am logged with "<user>" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/adherent_messages?scope=<scope>" with body:
            """
            {
                "sender": "021268fe-d4b3-44a7-bce9-c001191249a7",
                "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
                "subject": "L'objet de l'email",
                "content": "<table>...</table>",
                "json_content": "{\"foo\": \"bar\", \"items\": [1, 2, true, \"hello world\"]}"
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "@uuid@",
                "label": "Label du message qui permet de le retrouver dans la liste des messages envoyés",
                "subject": "L'objet de l'email",
                "status": "draft",
                "recipient_count": 0,
                "preview_link": null,
                "source": "vox",
                "synchronized": false,
                "author": {
                    "uuid": "@uuid@",
                    "first_name": "@string@",
                    "last_name": "@string@",
                    "image_url": null
                },
                "sender": {
                    "uuid": "@uuid@",
                    "first_name": "Bob",
                    "last_name": "Senateur (59)",
                    "image_url": null,
                    "instance": "Assemblée départementale",
                    "scope": null,
                    "role": "Responsable mobilisation",
                    "zone": "Seine-et-Marne, Hauts-de-Seine, Seine-Maritime, Nord, Bouches-du-Rhône",
                    "theme": {
                        "active": "#1C5CD8",
                        "hover": "#2F6FE0",
                        "primary": "#3A7DFF",
                        "soft": "#E8F0FF"
                    }
                },
                "updated_at": "@string@.isDateTime()"
            }
            """
        When I save this response
        And I send a "PUT" request to "/api/v3/adherent_messages/:saved_response.uuid:/filter?scope=<scope>" with body:
            """
            {
                "zone": "e3efe5c5-906e-11eb-a875-0242ac150002"
            }
            """
        And I send a "GET" request to "/api/v3/adherent_messages/:saved_response.uuid:/count-recipients?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "push": 4,
                "email": 6,
                "push_email": 2,
                "only_push": 4,
                "only_email": 4,
                "contacts": 8,
                "total": 12
            }
            """

        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | president_departmental_assembly                |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
