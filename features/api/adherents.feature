@api
Feature:
  In order to get adherents' information
  As a referent
  I should be able to acces adherents API data

  Scenario: As a non logged-in user I can not access the adherents count information
    When I am on "/api/statistics/adherents/count"
    Then the response status code should be 401

  Scenario: As an adherent I can not access the adherents count information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/statistics/adherents/count"
    Then the response status code should be 401

  Scenario: As a referent I can access the adherents count information
    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
      | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
      | grant_type    | client_credentials                           |
      | scope         | read:stats                                   |
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/statistics/adherents/count?referent=referent@en-marche-dev.fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "female":24,"male":40,"total":64
    }
    """

  Scenario: As a non logged-in user I can not access the managed by referent adherents count information
    When I am on "/api/statistics/adherents/count-by-referent-area"
    Then the response status code should be 401

  Scenario: As an adherent I can not access the managed by referent adherents count information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/statistics/adherents/count-by-referent-area"
    Then the response status code should be 401

  Scenario: As a referent I can access the managed by referent adherents count information
    Given I freeze the clock to "2018-04-17"
    Given I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
      | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
      | grant_type    | client_credentials                           |
      | scope         | read:stats                                   |
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/statistics/adherents/count-by-referent-area?referent=referent-75-77@en-marche-dev.fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "female":3,
      "male":6,
      "total":9,
      "adherents": [
          {"date": "2018-04", "total": 8},
          {"date": "2018-03", "total": 8},
          {"date": "2018-02", "total": 8},
          {"date": "2018-01", "total": 8},
          {"date": "2017-12", "total": 7},
          {"date": "2017-11", "total": 7}
      ],
      "committee_members": [
          {"date": "2018-04", "count": 3},
          {"date": "2018-03", "count": 3},
          {"date": "2018-02", "count": 2},
          {"date": "2018-01", "count": 2},
          {"date": "2017-12", "count": 2},
          {"date": "2017-11", "count": 2}
      ],
      "email_subscriptions": [
          {"date": "2018-04", "subscribed_emails_local_host": 0, "subscribed_emails_referents": 0},
          {"date": "2018-03", "subscribed_emails_local_host": 0, "subscribed_emails_referents": 0},
          {"date": "2018-02", "subscribed_emails_local_host": 4, "subscribed_emails_referents": 0},
          {"date": "2018-01", "subscribed_emails_local_host": 3, "subscribed_emails_referents": 0},
          {"date": "2017-12", "subscribed_emails_local_host": 2, "subscribed_emails_referents": 0},
          {"date": "2017-11", "subscribed_emails_local_host": 1, "subscribed_emails_referents": 0}
      ]
    }
    """

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
      "comments_cgu_accepted": false,
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
      "comments_cgu_accepted": false,
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
                "propertyPath": "nickname",
                "message": "Cette valeur est déjà utilisée."
            }
        ]
    }
    """

  Scenario: As a logged-in user I cannot set my nickname if it's too long
    Given I am logged as "jacques.picard@en-marche.fr"
    And I add "Content-Type" header equal to "application/json"
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
    And I add "Content-Type" header equal to "application/json"
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
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
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
            },
            "filter": {
                "type": "select"
            }
        },
        {
            "key": "first_name",
            "label": "Prénom",
            "filter": {
                "type": "string"
            }
        },
        {
            "key": "last_name",
            "label": "Nom",
            "filter": {
                "type": "string"
            }
        },
        {
            "key": "email_subscription",
            "label": "Abonné email",
            "type": "boolean",
            "filter": {
                "type": "boolean"
            }
        },
        {
            "key": "sms_subscription",
            "label": "Abonné tel",
            "type": "boolean",
            "filter": {
                "type": "boolean"
            }
        },
        {
            "key": "postal_code",
            "label": "Code postal",
            "filter": {
                "type": "string"
            }
        },
        {
            "key": "city_code",
            "label": "Code commune"
        },
        {
            "key": "city",
            "label": "Commune",
            "filter": {
                "type": "string"
            }
        },
        {
            "key": "department_code",
            "label": "Code département"
        },
        {
            "key": "department",
            "label": "Département"
        },
        {
            "key": "region_code",
            "label": "Code région"
        },
        {
            "key": "region",
            "label": "Région"
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
            },
            "filter": {
                "type": "select",
                "options": {
                    "multiple": true
                }
            }
        }
    ]
    """
    Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user with (delegated) referent role I can get filters list to filter adherents
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/adherents/filters?scope=<scope>"
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
            "options": null,
            "type": "integer_interval"
        },
        {
            "code": "registered",
            "label": "Adhésion",
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
            "code": "isCertified",
            "label": "Certifié",
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
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |

  Scenario Outline: As a user with (delegated) referent role I can get adherents of my zones
    Given I am logged with "<user>" via OAuth client "JeMengage Web"
    When I send a "GET" request to "/api/v3/adherents?scope=<scope>"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 4,
            "items_per_page": 100,
            "count": 4,
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
                "department_code": "77",
                "department": "Seine-et-Marne",
                "region_code": "11",
                "region": "Île-de-France",
                "sms_subscription": false,
                "email_subscription": false
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
                "department_code": null,
                "department": null,
                "region_code": null,
                "region": null,
                "sms_subscription": true,
                "email_subscription": true
            },
            {
                "postal_code": "92110",
                "city": "Clichy",
                "country": "FR",
                "first_name": "Gisele",
                "last_name": "Berthoux",
                "gender": "female",
                "interests": [],
                "city_code": null,
                "department_code": "92",
                "department": "Hauts-de-Seine",
                "region_code": "11",
                "region": "Île-de-France",
                "sms_subscription": true,
                "email_subscription": true
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
                "department_code": null,
                "department": null,
                "region_code": null,
                "region": null,
                "sms_subscription": true,
                "email_subscription": false
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
                "department_code": "77",
                "department": "Seine-et-Marne",
                "region_code": "11",
                "region": "Île-de-France",
                "sms_subscription": false,
                "email_subscription": false
            }
        ]
    }
    """
      Examples:
      | user                      | scope                                          |
      | referent@en-marche-dev.fr | referent                                       |
      | senateur@en-marche-dev.fr | delegated_08f40730-d807-4975-8773-69d8fae1da74 |
