@api
Feature:
  In order to get adherents information
  As a referent
  I should be able to access adherents API data

  Scenario: As an anonymous user I cannot access to my information
    And I am on "/api/users/me"
    Then the response status code should be 401

  Scenario: As a referent I can access to my information
    When I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "/api/users/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid": "2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf",
      "email_address": "referent-75-77@en-marche-dev.fr",
      "first_name": "Referent75and77",
      "last_name": "Referent75and77",
      "country": "FR",
      "postal_code": "75001",
      "managed_area_tag_codes": [
        "75008",
        "75009",
        "75",
        "77"
      ],
      "nickname": null,
      "use_nickname": false,
      "elected": false,
      "larem": false,
      "certified": false,
      "detailed_roles": [
        {
          "label": "ROLE_REFERENT",
          "codes": [
            "75008",
            "75009",
            "75",
            "77"
          ]
        },
        {
          "label": "ROLE_PRINT_PRIVILEGE"
        }
      ],
      "emailAddress": "referent-75-77@en-marche-dev.fr",
      "email_subscribed": true,
      "firstName": "Referent75and77",
      "lastName": "Referent75and77",
      "zipCode": "75001",
      "managedAreaTagCodes": [
        "75008",
        "75009",
        "75",
        "77"
      ]
    }
    """

  Scenario: As a standard adherent I can access to my information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/users/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "uuid":"a046adbe-9c7b-56a9-a676-6151a6785dda",
      "country":"FR",
      "postal_code":"75008",
      "email_address":"jacques.picard@en-marche.fr",
      "first_name":"Jacques",
      "last_name":"Picard",
      "nickname":"kikouslove",
      "use_nickname":true,
      "elected": false,
      "larem": true,
      "certified": true,
      "detailed_roles": [],
      "emailAddress":"jacques.picard@en-marche.fr",
      "email_subscribed": true,
      "firstName":"Jacques",
      "lastName":"Picard",
      "zipCode":"75008"
    }
    """

  Scenario: As a non logged-in user I can not set a nickname
    Given I send a "PUT" request to "/api/adherents/me/anonymize"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not set a nickname of another person
    Given I am logged as "jacques.picard@en-marche.fr"
    When I send a "PUT" request to "/api/adherents/me/anonymize" with body:
    """
    {
    }
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
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "nickname: Cette valeur est déjà utilisée.",
        "violations": [
            {
                "code": "@uuid@",
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
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "nickname: Vous devez saisir au maximum 25 caractères.",
        "violations": [
            {
                "code": "@uuid@",
                "propertyPath": "nickname",
                "message": "Vous devez saisir au maximum 25 caractères."
            }
        ]
    }
    """

  Scenario: As a logged-in user I cannot set my nickname if it contains not authorised caracters
    Given I am logged as "jacques.picard@en-marche.fr"
    And I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/adherents/me/anonymize" with body:
    """
    {
        "nickname": "La République En Marche !"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON node "detail" should be equal to "nickname: La syntaxe est incorrecte, le pseudo ne peut contenir que des chiffres, lettres, et les caractères _ et -"

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
    And I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/adherents/me/anonymize" with body:
    """
    {
        "nickname": "ne-w nick_name",
        "use_nickname": true
    }
    """
    Then the response status code should be 200
    And the response should be in JSON

  Scenario Outline: As a user with (delegated) referent role I can get columns to list adherents
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/adherents/columns?scope=<scope>"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        {
            "key": "gender",
            "label": "Genre",
            "type": "trans",
            "messages": {
                "female": "Femme",
                "male": "Homme",
                "other": "Autre"
            }
        },
        {
            "key": "first_name",
            "label": "Prénom"
        },
        {
            "key": "last_name",
            "label": "Nom"
        },
        {
            "key": "renaissance_membership",
            "label": "Renaissance",
            "type": "trans",
            "messages": {
                "adherent_re": "Adhérent",
                "sympathizer_re": "Sympathisant"
            }
        },
        {
            "key": "email_subscription",
            "label": "Abonné email",
            "type": "boolean"
        },
        {
            "key": "email",
            "label": "Email",
            "dependency": {
                "fields": [
                    {
                        "code": "email_subscription",
                        "valid_values": [
                            true
                        ]
                    }
                ],
                "mode": "color_invalid"
            }
        },
        {
            "key": "sms_subscription",
            "label": "Abonné tel",
            "type": "boolean"
        },
        {
            "key": "phone_number",
            "label": "Téléphone",
            "type": "trans",
            "messages": {
                "not_available": "Non disponible"
            },
            "dependency": {
                "fields": [
                    {
                        "code": "sms_subscription",
                        "valid_values": [
                            true
                        ]
                    }
                ],
                "mode": "color_invalid"
            }
        },
        {
            "key": "postal_code",
            "label": "Code postal"
        },
        {
            "key": "city_code",
            "label": "Code commune"
        },
        {
            "key": "city",
            "label": "Commune"
        },
        {
            "key": "interests",
            "label": "Intérêts",
            "type": "array|trans",
            "messages": {
                "culture": "Culture",
                "democratie": "Démocratie",
                "economie": "Économie",
                "education": "Éducation",
                "jeunesse": "Jeunesse",
                "egalite": "Égalité F/H",
                "europe": "Europe",
                "inclusion": "Inclusion",
                "international": "International",
                "justice": "Justice",
                "lgbt": "LGBT+",
                "numerique": "Numérique",
                "puissance_publique": "Puissance publique",
                "republique": "République",
                "ruralite": "Ruralité",
                "sante": "Santé",
                "securite_et_defense": "Sécurité et Défense",
                "solidarites": "Solidarités",
                "sport": "Sport",
                "transition_ecologique": "Transition écologique",
                "travail": "Travail",
                "villes_et_quartiers": "Villes et quartiers",
                "famille": "Famille"
            }
        }
    ]
    """
    Examples:
      | user                                    | scope                                           |
      | referent@en-marche-dev.fr               | referent                                        |
      | senateur@en-marche-dev.fr               | delegated_08f40730-d807-4975-8773-69d8fae1da74  |
      | senatorial-candidate@en-marche-dev.fr   | legislative_candidate                           |
      | gisele-berthoux@caramail.com            | delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c  |

  Scenario Outline: As a user with (delegated) referent role I can get adherents of my zones
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/adherents?scope=<scope>"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 5,
            "items_per_page": 100,
            "count": 5,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "postal_code": "77000",
                "city": "Melun",
                "country": "FR",
                "first_name": "Francis",
                "last_name": "Brioul",
                "gender": "male",
                "interests": [],
                "city_code": "77288",
                "phone_number": "not_available",
                "sms_subscription": false,
                "email": "francis.brioul@yahoo.com",
                "email_subscription": false,
                "renaissance_membership": null
            },
            {
                "postal_code": "92110",
                "city": "Clichy",
                "country": "FR",
                "first_name": "Gisele",
                "last_name": "Berthoux",
                "gender": "female",
                "interests": [],
                "city_code": "92024",
                "phone_number": "+33 6 66 66 66 66",
                "sms_subscription": true,
                "email": "gisele-berthoux@caramail.com",
                "email_subscription": true,
                "renaissance_membership": null
            },
            {
                "city": "Melun",
                "city_code": null,
                "country": "FR",
                "email": "je-mengage-user-1@en-marche-dev.fr",
                "email_subscription": false,
                "first_name": "Jules",
                "gender": "male",
                "interests": [],
                "last_name": "Fullstack",
                "postal_code": "77000",
                "phone_number": "not_available",
                "sms_subscription": false,
                "renaissance_membership": null
            },
            {
                "postal_code": "8802",
                "city": "Kilchberg",
                "country": "CH",
                "first_name": "Michel",
                "last_name": "VASSEUR",
                "gender": "male",
                "interests": [
                    "numerique"
                ],
                "city_code": null,
                "phone_number": "+33 6 66 66 66 66",
                "email": "michel.vasseur@example.ch",
                "email_subscription": true,
                "sms_subscription": true,
                "renaissance_membership": null
            },
            {
                "postal_code": "8057",
                "city": "Zürich",
                "country": "CH",
                "first_name": "Michelle",
                "last_name": "Dufour",
                "gender": "male",
                "interests": [
                    "europe",
                    "numerique",
                    "sante"
                ],
                "city_code": null,
                "phone_number": "+33 6 66 66 66 66",
                "sms_subscription": true,
                "email": "michelle.dufour@example.ch",
                "email_subscription": false,
                "renaissance_membership": null
            }
        ]
    }
    """
    When I send a "GET" request to "/api/v3/adherents?scope=<scope>&firstName=Francis&lastName=Brioul&gender=male&registered%5Bstart%5D=2016-01-01&registered%5Bend%5D=2042-01-01&age%5Bmin%5D=18&age%5Bmax%5D=100&isCommitteeMember=1&isCertified=0&emailSubscription=0&smsSubscription=0"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 100,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "postal_code": "77000",
                "city": "Melun",
                "country": "FR",
                "first_name": "Francis",
                "last_name": "Brioul",
                "gender": "male",
                "interests": [],
                "city_code": "77288",
                "phone_number": "not_available",
                "sms_subscription": false,
                "email": "francis.brioul@yahoo.com",
                "email_subscription": false,
                "renaissance_membership": null
            }
        ]
    }
    """
      Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
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
            "items_per_page": 100,
            "count": 2,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "postal_code": "77000",
                "city": "Melun",
                "country": "FR",
                "first_name": "Jules",
                "last_name": "Fullstack",
                "gender": "male",
                "interests": [],
                "city_code": null,
                "phone_number": "not_available",
                "sms_subscription": false,
                "email": "je-mengage-user-1@en-marche-dev.fr",
                "email_subscription": false,
                "renaissance_membership": null
            },
            {
                "postal_code": "92110",
                "city": "Clichy",
                "country": "FR",
                "first_name": "Gisele",
                "last_name": "Berthoux",
                "gender": "female",
                "interests": [],
                "city_code": "92024",
                "phone_number": "+33 6 66 66 66 66",
                "sms_subscription": true,
                "email": "gisele-berthoux@caramail.com",
                "email_subscription": true,
                "renaissance_membership": null
            }
        ]
    }
    """
    When I send a "GET" request to "/api/v3/adherents?scope=correspondent&onlyJeMengageUsers=1"
    Then the response status code should be 200
    And the JSON nodes should match:
      | metadata.total_items  | 1 |

  Scenario Outline: As a user with (delegated) legislative candidate role I can get filters list to filter adherents
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/filters?scope=<scope>&feature=contacts"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
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
            "code": "registered",
            "label": "Inscrit",
            "options": null,
            "type": "date_interval"
        },
        {
            "code": "isCommitteeMember",
            "label": "Membre d'un comité",
            "options": {
                "choices": [
                    "Non",
                    "Oui"
                ]
            },
            "type": "select"
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
            "code": "onlyJeMengageUsers",
            "label": "Compte de la majorité présidentielle",
            "options": {
                "choices": [
                    "Non",
                    "Oui"
                ]
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
        },
        {
            "code": "smsSubscription",
            "label": "Abonné SMS",
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
            "type": "autocomplete"
        }
    ]
    """
    Examples:
      | user                                    | scope                                           |
      | senatorial-candidate@en-marche-dev.fr   | legislative_candidate                           |
      | gisele-berthoux@caramail.com            | delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c  |

  Scenario Outline: As a user with (delegated) legislative candidate role I can get adherents of my zones
    Given I am logged with "<user>" via OAuth client "JeMengage Web" with scope "jemengage_admin"
    When I send a "GET" request to "/api/v3/adherents?scope=<scope>"
    Then the response status code should be 200
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
                "postal_code": "75008",
                "city": "Paris 8ème",
                "country": "FR",
                "first_name": "Député",
                "last_name": "PARIS I",
                "gender": "male",
                "interests": [
                    "europe",
                    "numerique"
                ],
                "city_code": "75056",
                "phone_number": "not_available",
                "sms_subscription": true,
                "email": "deputy@en-marche-dev.fr",
                "email_subscription": true,
                "renaissance_membership": null
            },
            {
                "postal_code": "75008",
                "city": "Paris 8ème",
                "country": "FR",
                "first_name": "Jacques",
                "last_name": "Picard",
                "gender": "male",
                "interests": [
                    "europe",
                    "numerique",
                    "sante"
                ],
                "city_code": "75056",
                "phone_number": "+33 1 87 26 42 36",
                "sms_subscription": true,
                "email": "jacques.picard@en-marche.fr",
                "email_subscription": true,
                "renaissance_membership": null
            }
        ]
    }
    """
    When I send a "GET" request to "/api/v3/adherents?scope=<scope>&firstName=Jacques&lastName=Picard&gender=male&registered%5Bstart%5D=2017-01-01&registered%5Bend%5D=2022-01-01&age%5Bmin%5D=25&age%5Bmax%5D=90&isCommitteeMember=1&isCertified=1&emailSubscription=1&smsSubscription=1"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 100,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "postal_code": "75008",
                "city": "Paris 8ème",
                "country": "FR",
                "first_name": "Jacques",
                "last_name": "Picard",
                "gender": "male",
                "interests": [
                    "europe",
                    "numerique",
                    "sante"
                ],
                "city_code": "75056",
                "phone_number": "+33 1 87 26 42 36",
                "sms_subscription": true,
                "email": "jacques.picard@en-marche.fr",
                "email_subscription": true,
                "renaissance_membership": null
            }
        ]
    }
    """
    When I send a "GET" request to "/api/v3/adherents?scope=<scope>&onlyJeMengageUsers=1"
    Then the response status code should be 200
    And the JSON nodes should match:
      | metadata.total_items  | 0 |
    When I send a "GET" request to "/api/v3/adherents?scope=<scope>&onlyJeMengageUsers=0"
    Then the response status code should be 200
    And the JSON nodes should match:
      | metadata.total_items  | 2 |
    Examples:
      | user                                    | scope                                           |
      | senatorial-candidate@en-marche-dev.fr   | legislative_candidate                           |
      | gisele-berthoux@caramail.com            | delegated_b24fea43-ecd8-4bf4-b500-6f97886ab77c  |

    Scenario: I can count all adherent RE in my zone
        Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMengage Web" with scope "jemengage_admin"
        When I send a "GET" request to "/api/v3/adherents/count?scope=referent"
        Then the response status code should be 200
        And the JSON should be equal to:
        """
        {
            "adherent": 5,
            "sympathizer": 0
        }
        """
        When I send a "POST" request to "/api/v3/adherents/count?scope=referent" with body:
        """
        ["e3efe6fd-906e-11eb-a875-0242ac150002"]
        """
        Then the response status code should be 200
        And the JSON should be equal to:
        """
        {
            "adherent": 1,
            "sympathizer": 0
        }
        """
