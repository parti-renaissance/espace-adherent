@api
@renaissance
Feature:
  In order to manage elected representatives
  As a logged-in user
  I should be able to access elected representatives API

  Scenario Outline: As a user granted with local scope, I can get elected representatives in a zone I am manager of
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "GET" request to "/api/v3/elected_representatives?scope=<scope>&renaissanceMembership=adherent_re"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 2,
            "items_per_page": 100,
            "count": 2,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "last_name": "92",
                "first_name": "Département",
                "gender": "male",
                "contributed_at": "@string@.isDateTime()",
                "contribution_status": "eligible",
                "last_contribution": {
                    "end_date": null,
                    "start_date": "@string@.isDateTime()",
                    "status": "active",
                    "type": "mandate",
                    "uuid": "117921c2-93ce-4307-8364-709fd34de79c"
                },
                "contact_phone": null,
                "uuid": "0c62d201-826b-4da7-8424-e8e17935b400",
                "current_mandates": [
                    {
                        "type": "senateur",
                        "geo_zone": {
                            "uuid": "@uuid@",
                            "code": "92",
                            "name": "Hauts-de-Seine"
                        },
                        "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727"
                    }
                ],
                "current_political_functions": []
            },
            {
                "last_name": "DUFOUR",
                "first_name": "Michelle",
                "gender": "female",
                "contributed_at": null,
                "contribution_status": null,
                "last_contribution": null,
                "contact_phone": null,
                "uuid": "34b0b236-b72e-4161-8f9f-7f23f935758f",
                "current_mandates": [
                    {
                        "type": "conseiller_municipal",
                        "geo_zone": {
                            "uuid": "@uuid@",
                            "code": "200054781",
                            "name": "Métropole du Grand Paris"
                        },
                        "uuid": "b2afc81d-afd5-4bff-84e5-c95f22242244"
                    }
                ],
                "current_political_functions": [
                    {
                        "name": "other_member"
                    }
                ]
            }
        ]
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can create an elected representative in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "POST" request to "/api/v3/elected_representatives?scope=<scope>" with body:
        """
        {
            "last_name": "Doe",
            "first_name": "John",
            "gender": "male",
            "birth_date": "1990-02-02",
            "adherent": "29461c49-2646-4d89-9c82-50b3f9b586f4"
        }
        """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "last_name": "Doe",
            "first_name": "John",
            "gender": "male",
            "contributed_at": null,
            "contribution_status": null,
            "last_contribution": null,
            "birth_date": "@string@.isDateTime()",
            "birth_place": null,
            "contact_phone": null,
            "has_followed_training": false,
            "adherent": {
                "email_address": "referent@en-marche-dev.fr",
                "phone": "+33 6 73 65 43 49",
                "uuid": "29461c49-2646-4d89-9c82-50b3f9b586f4",
                "first_name": "Referent",
                "last_name": "Referent"
            },
            "mandates": [],
            "uuid": "@uuid@",
            "email_address": "referent@en-marche-dev.fr",
            "payments": []
        }
        """
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | referent                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can update an elected representative in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "PUT" request to "/api/v3/elected_representatives/0c62d201-826b-4da7-8424-e8e17935b400?scope=<scope>" with body:
        """
        {
            "last_name": "Doe",
            "first_name": "Jane"
        }
        """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "last_name": "Doe",
            "first_name": "Jane",
            "gender": "male",
            "contributed_at": "@string@.isDateTime()",
            "contribution_status": "eligible",
            "last_contribution": {
                "end_date": null,
                "start_date": "@string@.isDateTime()",
                "status": "active",
                "type": "mandate",
                "uuid": "117921c2-93ce-4307-8364-709fd34de79c"
            },
            "birth_date": "@string@.isDateTime()",
            "birth_place": null,
            "contact_phone": null,
            "has_followed_training": false,
            "adherent": {
                "email_address": "renaissance-user-2@en-marche-dev.fr",
                "phone": null,
                "uuid": "d0a0935f-da7c-4caa-b582-a8c2376e5158",
                "first_name": "John",
                "last_name": "Smith"
            },
            "mandates": [
                {
                    "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727",
                    "type": "senateur",
                    "is_elected": true,
                    "geo_zone": {
                        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                        "code": "92",
                        "name": "Hauts-de-Seine"
                    },
                    "on_going": true,
                    "begin_at": "@string@.isDateTime()",
                    "finish_at": null,
                    "political_affiliation": "REM",
                    "la_r_e_m_support": "official",
                    "political_functions": []
                }
            ],
            "uuid": "0c62d201-826b-4da7-8424-e8e17935b400",
            "email_address": "renaissance-user-2@en-marche-dev.fr",
            "payments": [
                {
                    "date": "@string@.isDateTime()",
                    "method": "IBAN",
                    "status": "confirmed",
                    "amount": 50,
                    "uuid": "@uuid@"
                },
                {
                    "date": "@string@.isDateTime()",
                    "method": "IBAN",
                    "status": "confirmed",
                    "amount": 50,
                    "uuid": "@uuid@"
                }
            ]
        }
        """
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | referent                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

    Scenario Outline: As a user granted with local scope, I can get an elected representative informations in a zone I am manager of
        Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/elected_representatives/0c62d201-826b-4da7-8424-e8e17935b400?scope=<scope>"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
        """
        {
            "first_name": "Département",
            "last_name": "92",
            "gender": "male",
            "contributed_at": "@string@.isDateTime()",
            "contribution_status": "eligible",
            "last_contribution": {
                "end_date": null,
                "start_date": "@string@.isDateTime()",
                "status": "active",
                "type": "mandate",
                "uuid": "117921c2-93ce-4307-8364-709fd34de79c"
            },
            "birth_date": "@string@.isDateTime()",
            "birth_place": null,
            "contact_phone": null,
            "has_followed_training": false,
            "adherent": {
                "email_address": "renaissance-user-2@en-marche-dev.fr",
                "phone": null,
                "uuid": "d0a0935f-da7c-4caa-b582-a8c2376e5158",
                "first_name": "John",
                "last_name": "Smith"
            },
            "mandates": [
                {
                    "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727",
                    "type": "senateur",
                    "is_elected": true,
                    "geo_zone": {
                        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
                        "code": "92",
                        "name": "Hauts-de-Seine"
                    },
                    "on_going": true,
                    "begin_at": "@string@.isDateTime()",
                    "finish_at": null,
                    "political_affiliation": "REM",
                    "la_r_e_m_support": "official",
                    "political_functions": []
                }
            ],
            "uuid": "0c62d201-826b-4da7-8424-e8e17935b400",
            "email_address": "renaissance-user-2@en-marche-dev.fr",
            "payments": [
                {
                    "date": "@string@.isDateTime()",
                    "method": "IBAN",
                    "status": "confirmed",
                    "amount": 50,
                    "uuid": "@uuid@"
                },
                {
                    "date": "@string@.isDateTime()",
                    "method": "IBAN",
                    "status": "confirmed",
                    "amount": 50,
                    "uuid": "@uuid@"
                }
            ]
        }
        """
        Examples:
            | user                      | scope                                          |
            | referent@en-marche-dev.fr | referent                                       |
            | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can delete an elected representative created by me
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    And I send a "DELETE" request to "/api/v3/elected_representatives/0c62d201-826b-4da7-8424-e8e17935b400?scope=<scope>"
    Then the response status code should be 204

    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user with (delegated) referent role I can get filters list to filter elected representatives
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/filters?scope=<scope>&feature=elected_representative"
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
                    "label": "Genre",
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
                    "code": "emailSubscription",
                    "label": "Abonné email",
                    "options": {
                        "choices": [
                            "Non",
                            "Oui"
                        ]
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
                    "label": "Tags adhérent",
                    "options": {
                        "choices": {
                            "adherent": "Adhérent",
                            "adherent:cotisation_nok": "Adhérent - non à jour de cotisation",
                            "adherent:cotisation_ok": "Adhérent - à jour de cotisation",
                            "sympathisant": "Sympathisant",
                            "sympathisant:compte_em": "Sympathisant - ancien adhérent EM",
                            "sympathisant:compte_re": "Sympathisant - adhésion incomplète"
                        }
                    },
                    "type": "select"
                  },
                {
                    "code": "committeeUuids",
                    "label": "Comités",
                    "options": {
                        "choices": {
                            "5e00c264-1d4b-43b8-862e-29edc38389b3": "Comité des 3 communes",
                            "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3": "Second Comité des 3 communes"
                        },
                        "multiple": true,
                        "required": false
                    },
                    "type": "select"
                },
                {
                    "code": "renaissance_membership",
                    "label": "Renaissance",
                    "options": {
                        "choices": {
                            "adherent_or_sympathizer_re": "Adhérent RE ou sympathisant RE",
                            "adherent_re": "Adhérent RE seulement",
                            "sympathizer_re": "Sympathisant RE seulement",
                            "others_adherent": "Ni adhérent RE ni sympathisant RE"
                        }
                    },
                    "type": "select"
                }
            ]
        },
        {
            "label": "Élu",
            "color": "#2563EB",
            "filters": [
                {
                    "code": "elect_tags",
                    "label": "Tags élu",
                    "options": {
                        "choices": {
                            "cotisation_eligible": "À jour, soumis à cotisation",
                            "cotisation_exempt": "À jour, exempté de cotisation",
                            "cotisation_nok": "Pas à jour de cotisation élu",
                            "cotisation_not_eligible": "À jour, non soumis à cotisation",
                            "cotisation_ok": "À jour de cotisation élu",
                            "undeclared_revenue": "En attente de déclaration"
                        }
                    },
                    "type": "select"
                },
                {
                    "code": "political_functions",
                    "label": "Fonctions",
                    "options": {
                        "choices": {
                            "mayor": "Maire",
                            "deputy_mayor": "Maire délégué(e)",
                            "mayor_assistant": "Adjoint(e) au maire",
                            "president_of_regional_council": "Président(e) de conseil régional",
                            "vice_president_of_regional_council": "Vice-président(e) de conseil régional",
                            "president_of_departmental_council": "Président(e) de conseil départemental",
                            "vice_president_of_departmental_council": "Vice-président(e) de conseil départemental",
                            "deputy_vice_president_of_departmental_council": "Vice-président(e) délégué du conseil départemental",
                            "secretary": "Secrétaire",
                            "quaestor": "Questeur(rice)",
                            "president_of_national_assembly": "Président(e) de l'Assemblée nationale",
                            "vice_president_of_national_assembly": "Vice-président(e) de l'Assemblée nationale",
                            "president_of_senate": "Président(e) du Sénat",
                            "vice_president_of_senate": "Vice-président(e) du Sénat",
                            "president_of_commission": "Président(e) de commission",
                            "president_of_group": "Président(e) de groupe",
                            "president_of_epci": "Président(e) d'EPCI",
                            "vice_president_of_epci": "Vice-président(e) d'EPCI",
                            "other_member_of_standing_committee": "Autre membre commission permanente",
                            "other_member": "Autre membre"
                        },
                        "multiple": true
                    },
                    "type": "select"
                },
                {
                    "code": "revenueDeclared",
                    "label": "Indemnités déclarées",
                    "options": {
                        "choices": [
                            "Non",
                            "Oui"
                        ]
                    },
                    "type": "select"
                },
                {
                    "code": "contributionActive",
                    "label": "Cotisation active",
                    "options": {
                        "choices": [
                            "Non",
                            "Oui"
                        ]
                    },
                    "type": "select"
                }
            ]
        }
    ]
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can get an elected representative mandate informations
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/elected_mandates/9051e0b5-4b56-41b9-8657-cc45e431c727?scope=<scope>"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "senateur",
      "is_elected": true,
      "geo_zone": {
        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
        "code": "92",
        "name": "Hauts-de-Seine"
      },
      "on_going": true,
      "begin_at": "2019-01-11T00:00:00+01:00",
      "finish_at": null,
      "political_affiliation": "REM",
      "la_r_e_m_support": "official",
      "elected_representative": {
        "last_name": "92",
        "first_name": "Département",
        "uuid": "0c62d201-826b-4da7-8424-e8e17935b400"
      },
      "political_functions": [],
      "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727"
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can create an elected representative mandate informations
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "POST" request to "/api/v3/elected_mandates?scope=<scope>" with body:
    """
    {
      "type": "membre_EPCI",
      "is_elected": true,
      "geo_zone": "e3efe6fd-906e-11eb-a875-0242ac150002",
      "on_going": true,
      "begin_at": "2023-01-13",
      "finish_at": null,
      "political_affiliation": "REM",
      "la_r_e_m_support": "informal",
      "elected_representative": "0c62d201-826b-4da7-8424-e8e17935b400",
      "political_functions": [
          {
              "name": "president_of_epci",
              "clarification": "string",
              "on_going": true,
              "begin_at": "2023-01-30T12:01:51.575Z",
              "finish_at": null
          },
          {
              "name": "vice_president_of_epci",
              "clarification": "string",
              "on_going": false,
              "begin_at": "2023-01-13T12:01:51.575Z",
              "finish_at": "2023-01-30T11:01:51.575Z"
          }
      ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "membre_EPCI",
      "is_elected": true,
      "geo_zone": {
        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
        "code": "92",
        "name": "Hauts-de-Seine"
      },
      "on_going": true,
      "begin_at": "2023-01-13T00:00:00+01:00",
      "finish_at": null,
      "political_affiliation": "REM",
      "la_r_e_m_support": "informal",
      "elected_representative": {
        "last_name": "92",
        "first_name": "Département",
        "uuid": "0c62d201-826b-4da7-8424-e8e17935b400"
      },
      "political_functions": [
        {
          "id": @integer@,
          "name": "president_of_epci",
          "clarification": "string",
          "on_going": true,
          "begin_at": "2023-01-30T12:01:51+00:00",
          "finish_at": null
        },
        {
          "id": @integer@,
          "name": "vice_president_of_epci",
          "clarification": "string",
          "on_going": false,
          "begin_at": "2023-01-13T12:01:51+00:00",
          "finish_at": "2023-01-30T11:01:51+00:00"
        }
      ],
      "uuid": "@uuid@"
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can update an elected representative mandate informations
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "PUT" request to "/api/v3/elected_mandates/9051e0b5-4b56-41b9-8657-cc45e431c727?scope=<scope>" with body:
    """
    {
      "type": "membre_EPCI",
      "is_elected": true,
      "geo_zone": "e3efe6fd-906e-11eb-a875-0242ac150002",
      "on_going": true,
      "begin_at": "2023-01-13T00:00:00+01:00",
      "finish_at": null,
      "political_affiliation": "REM",
      "la_r_e_m_support": "informal",
      "elected_representative": "0c62d201-826b-4da7-8424-e8e17935b400",
      "political_functions": [
        {
          "name": "president_of_epci",
          "clarification": "test",
          "on_going": true,
          "begin_at": "2023-01-30T12:01:51+00:00",
          "finish_at": null
        },
        {
          "name": "vice_president_of_epci",
          "clarification": "test",
          "on_going": false,
          "begin_at": "2023-01-13T12:01:51+00:00",
          "finish_at": "2023-01-30T11:01:51+00:00"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "membre_EPCI",
      "is_elected": true,
      "geo_zone": {
        "uuid": "e3efe6fd-906e-11eb-a875-0242ac150002",
        "code": "92",
        "name": "Hauts-de-Seine"
      },
      "on_going": true,
      "begin_at": "2023-01-13T00:00:00+01:00",
      "finish_at": null,
      "political_affiliation": "REM",
      "la_r_e_m_support": "informal",
      "elected_representative": {
        "last_name": "92",
        "first_name": "Département",
        "uuid": "0c62d201-826b-4da7-8424-e8e17935b400"
      },
      "political_functions": [
        {
          "id": @integer@,
          "name": "president_of_epci",
          "clarification": "test",
          "on_going": true,
          "begin_at": "2023-01-30T12:01:51+00:00",
          "finish_at": null
        },
        {
          "id": @integer@,
          "name": "vice_president_of_epci",
          "clarification": "test",
          "on_going": false,
          "begin_at": "2023-01-13T12:01:51+00:00",
          "finish_at": "2023-01-30T11:01:51+00:00"
        }
      ],
      "uuid": "9051e0b5-4b56-41b9-8657-cc45e431c727"
    }
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user granted with local scope, I can delete an elected representative mandate informations
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "DELETE" request to "/api/v3/elected_mandates/9051e0b5-4b56-41b9-8657-cc45e431c727?scope=<scope>"
    Then the response status code should be 204
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
