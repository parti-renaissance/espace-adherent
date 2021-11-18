@apiUserInfo
Feature:
  As a logged-in user
  I should be able to retrieve and edit my profile information

  Background:
    Given the following fixtures are loaded:
      | LoadClientData        |
      | LoadOAuthTokenData    |
      | LoadUserData          |

  Scenario: As a non logged-in user I cannot get my profile information
    When I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 401

  Scenario: As a logged-in user I can retrieve and update my profile information
    Given I am logged with "carl999@example.fr" via OAuth client "Coalition App" with scopes "read:profile write:profile"
    When I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
        "email_address": "carl999@example.fr",
        "first_name": "Carl",
        "last_name": "Mirabeau",
        "gender": "male",
        "custom_gender": null,
        "nationality": "FR",
        "post_address": {
            "address": "122 rue de Mouxy",
            "postal_code": "73100",
            "city": "73100-73182",
            "city_name": "Mouxy",
            "region": null,
            "country": "FR"
        },
        "phone": {
          "country": "FR",
          "number": "01 11 22 33 44"
        },
        "birthdate": "1950-07-08T00:00:00+01:00",
        "certified": false,
        "facebook_page_url": null,
        "twitter_page_url": null,
        "linkedin_page_url": null,
        "telegram_page_url": null,
        "position": "retired",
        "job": null,
        "activity_area": null,
        "subscription_types": [
            {
                "label": "Recevoir les e-mails nationaux",
                "code": "subscribed_emails_movement_information"
            },
            {
                "label": "Recevoir la newsletter hebdomadaire nationale",
                "code": "subscribed_emails_weekly_letter"
            },
            {
                "label": "Recevoir les e-mails de mes candidat(e)s LaREM",
                "code": "candidate_email"
            },
            {
                "label": "Recevoir les e-mails de mon/ma député(e)",
                "code": "deputy_email"
            },
            {
                "label": "Recevoir les e-mails de mon/ma référent(e) territorial(e)",
                "code": "subscribed_emails_referents"
            },
            {
                "label": "Recevoir les e-mails de mon/ma sénateur/trice",
                "code": "senator_email"
            }
        ],
        "interests": [],
        "coalition_subscription": false,
        "cause_subscription": false,
        "coalitions_cgu_accepted": false,
        "adherent": true
    }
    """

    # Update post address
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "address": {
          "address": "10 rue inconnue",
          "postal_code": "12345",
          "city_name": "Ville inconnue",
          "country": "FR"
      }
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "address",
          "message": "Cette valeur n'est pas un code postal français valide."
        },
        {
          "propertyPath": "address",
          "message": "Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "address": {
          "address": "50 rue de la villette",
          "postal_code": "69003",
          "city_name": "Lyon 3e",
          "country": "FR"
      }
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "post_address": {
          "address": "50 rue de la villette",
          "postal_code": "69003",
          "city": "69003-69383",
          "city_name": "Lyon 3e",
          "country": "FR"
      }
    }
    """

    # Update gender
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "gender": "unknown_gender",
      "custom_gender": null
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "gender",
          "message": "Ce sexe n'est pas valide."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "gender": "female",
      "custom_gender": null
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "gender": "female",
      "custom_gender": null
    }
    """

    # Update custom gender
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "gender": "other",
      "custom_gender": "Apache"
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "gender": "other",
      "custom_gender": "Apache"
    }
    """

    # Update first name and last name
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "first_name": "J",
      "last_name": "D"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "first_name",
          "message": "Votre prénom doit comporter au moins 2 caractères."
        },
        {
          "propertyPath": "last_name",
          "message": "Votre prénom doit comporter au moins 2 caractères."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "first_name": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
      "last_name": "Suspendisse facilisis non leo id maximus. Fusce quis ligula quam."
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "first_name",
          "message": "Votre prénom ne peut pas dépasser 50 caractères."
        },
        {
          "propertyPath": "last_name",
          "message": "Votre prénom ne peut pas dépasser 50 caractères."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "first_name": "",
      "last_name": ""
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "first_name",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "propertyPath": "last_name",
          "message": "Cette valeur ne doit pas être vide."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "first_name": "John",
      "last_name": "Doe"
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "first_name": "John",
      "last_name": "Doe"
    }
    """

    # Update interests
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "interests": [
        "unknown_interest_code"
      ]
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "interests",
          "message": "Valeur d'intérêt n'est pas valide"
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "interests": [
        "egalite",
        "numerique"
      ]
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "interests": [
        {
          "label": "Égalité F/H",
          "code": "egalite"
        },
        {
          "label": "Numérique",
          "code": "numerique"
        }
      ]
    }
    """

    # Update subscription types
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "subscription_types": [
        "unknown_subscription_code"
      ]
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "subscription_types",
          "message": "Une ou plusieurs des valeurs de la suscription aux notifications sont invalides. Merci de contacter le support."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "subscription_types": [
        "militant_action_sms",
        "subscribed_emails_movement_information"
      ]
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "subscription_types": [
        {
          "label": "Recevoir les informations sur les actions militantes du mouvement par téléphone",
          "code": "militant_action_sms"
        },
        {
          "label": "Recevoir les e-mails nationaux",
          "code": "subscribed_emails_movement_information"
        }
      ]
    }
    """

    # Update professional related fields
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "position": "unknown_position_code",
      "job": "unknown_job",
      "activity_area": "unknown_activity_area"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "position",
          "message": "Le statut d'activité n'est pas valide."
        },
        {
          "propertyPath": "job",
          "message": "Le métier n'est pas valide."
        },
        {
          "propertyPath": "activity_area",
          "message": "Le secteur d'activité n'est pas valide."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "position": "employed",
      "job": "Ingénieurs",
      "activity_area": "Informatique et électronique"
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "position": "employed",
      "job": "Ingénieurs",
      "activity_area": "Informatique et électronique"
    }
    """

    # Update nationality
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "nationality": "AA"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "nationality",
          "message": "Cette nationalité n'est pas valide."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "nationality": "DE"
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "nationality": "DE"
    }
    """

    # Update birthdate
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "birthdate": "1988-11-27"
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "birthdate": "1988-11-27T00:00:00+01:00"
    }
    """

    # Update coalition & cause subscriptions
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "coalition_subscription": true,
      "cause_subscription": true
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "coalition_subscription": true,
      "cause_subscription": true
    }
    """

    # Update coalitions cgu acceptation
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "coalitions_cgu_accepted": true
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "coalitions_cgu_accepted": true
    }
    """

    # Update phone number
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "phone": {
        "country": "FR",
        "number": "12345678900000000"
      }
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "phone",
          "message": "Cette valeur n'est pas un numéro de téléphone valide."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "phone": {
        "country": "FR",
        "number": "0987654321"
      }
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "phone": {
        "country": "FR",
        "number": "09 87 65 43 21"
      }
    }
    """

    # Update social links
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "facebook_page_url": "https://not-facebook.com/johndoe",
      "twitter_page_url": "https://not-twitter.com/johndoe",
      "linkedin_page_url": "https://not-linkedin.com/johndoe",
      "telegram_page_url": "https://not-t.me/johndoe"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
        {
          "propertyPath": "facebook_page_url",
          "message": "Cette URL ne semble pas être une URL Facebook valide."
        },
        {
          "propertyPath": "twitter_page_url",
          "message": "Cette URL ne semble pas être une URL Twitter valide."
        },
        {
          "propertyPath": "linkedin_page_url",
          "message": "Cette URL ne semble pas être une URL LinkedIn valide."
        },
        {
          "propertyPath": "telegram_page_url",
          "message": "Cette URL ne semble pas être une URL Telegram valide."
        }
      ]
    }
    """
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "facebook_page_url": "https://facebook.com/johndoe",
      "twitter_page_url": "https://twitter.com/johndoe",
      "linkedin_page_url": "https://linkedin.com/johndoe",
      "telegram_page_url": "https://t.me/johndoe"
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "facebook_page_url": "https://facebook.com/johndoe",
      "twitter_page_url": "https://twitter.com/johndoe",
      "linkedin_page_url": "https://linkedin.com/johndoe",
      "telegram_page_url": "https://t.me/johndoe"
    }
    """

    # Update email address
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "email_address": "invalid_email"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "violations": [
          {
              "propertyPath": "email_address",
              "message": "Ceci n'est pas une adresse e-mail valide."
          }
      ]
    }
    """
    Given I should have 0 email
    When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
    """
    {
      "email_address": "new.mail@example.com"
    }
    """
    Then the response status code should be 200
    And I should have 1 email "AdherentChangeEmailMessage" for "new.mail@example.com" with payload:
    """
    {
      "template_name": "adherent-change-email",
      "template_content": [],
      "message": {
        "subject": "Validez votre nouvelle adresse e-mail",
        "from_email": "contact@en-marche.fr",
        "merge_vars": [
          {
            "rcpt": "new.mail@example.com",
            "vars": [
              {
                "name": "first_name",
                "content": "John"
              },
              {
                "name": "activation_link",
                "content": "@string@.isUrl()"
              }
            ]
          }
        ],
        "from_name": "La R\u00e9publique En Marche !",
        "to": [
          {
            "email": "new.mail@example.com",
            "type": "to",
            "name": "John Doe"
          }
        ]
      }
    }
    """

    # Email address does not change until new email address is validated
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "email_address": "carl999@example.fr"
    }
    """

  Scenario: As a non-adherent logged-in user I can update my profile with different validation rules
    Given I am logged with "simple-user@example.ch" via OAuth client "Coalition App" with scopes "read:profile write:profile"
    When I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    Then print last JSON response
    And the JSON should be equal to:
    """
    {
    "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9699",
    "first_name": "Simple",
    "last_name": "User",
    "certified": false,
    "gender": null,
    "custom_gender": null,
    "email_address": "simple-user@example.ch",
    "phone": null,
    "birthdate": null,
    "position": "employed",
    "subscription_types": [],
    "adherent": false,
    "facebook_page_url": null,
    "twitter_page_url": null,
    "linkedin_page_url": null,
    "telegram_page_url": null,
    "job": null,
    "activity_area": null,
    "nationality": null,
    "coalition_subscription": false,
    "cause_subscription": false,
    "coalitions_cgu_accepted": false,
    "post_address": {
      "address": "",
      "postal_code": "8057",
      "city": null,
      "city_name": null,
      "country": "CH",
      "region": null
    },
    "interests": []
    }
    """

    # Update blank last name
    When I send a "PUT" request to "/api/v3/profile/313bd28f-efc8-57c9-8ab7-2106c8be9699" with body:
    """
    {
      "last_name": ""
    }
    """
    Then the response status code should be 200
    Then I send a "GET" request to "/api/v3/profile/me"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be a superset of:
    """
    {
      "last_name": ""
    }
    """

  Scenario: As a logged-in user I can retrieve the profile available configuration
    Given I am logged with "carl999@example.fr" via OAuth client "Coalition App" with scopes "write:profile"
    When I send a "GET" request to "/api/v3/profile/configuration"
    Then the response status code should be 200
    And the response should be in JSON
    Then print last JSON response
    And the JSON should be equal to:
    """
    {
      "interests": [
        {
          "code": "culture",
          "label": "Culture"
        },
        {
          "code": "democratie",
          "label": "Démocratie"
        },
        {
          "code": "economie",
          "label": "Économie"
        },
        {
          "code": "education",
          "label": "Éducation"
        },
        {
          "code": "jeunesse",
          "label": "Jeunesse"
        },
        {
          "code": "egalite",
          "label": "Égalité F/H"
        },
        {
          "code": "europe",
          "label": "Europe"
        },
        {
          "code": "inclusion",
          "label": "Inclusion"
        },
        {
          "code": "international",
          "label": "International"
        },
        {
          "code": "justice",
          "label": "Justice"
        },
        {
          "code": "lgbt",
          "label": "LGBT+"
        },
        {
          "code": "numerique",
          "label": "Numérique"
        },
        {
          "code": "puissance_publique",
          "label": "Puissance publique"
        },
        {
          "code": "republique",
          "label": "République"
        },
        {
          "code": "ruralite",
          "label": "Ruralité"
        },
        {
          "code": "sante",
          "label": "Santé"
        },
        {
          "code": "securite_et_defense",
          "label": "Sécurité et Défense"
        },
        {
          "code": "solidarites",
          "label": "Solidarités"
        },
        {
          "code": "sport",
          "label": "Sport"
        },
        {
          "code": "transition_ecologique",
          "label": "Transition écologique"
        },
        {
          "code": "travail",
          "label": "Travail"
        },
        {
          "code": "villes_et_quartiers",
          "label": "Villes et quartiers"
        },
        {
          "code": "famille",
          "label": "Famille"
        }
      ],
      "subscription_types": [
        {
          "code": "militant_action_sms",
          "label": "Recevoir les informations sur les actions militantes du mouvement par téléphone"
        },
        {
          "code": "subscribed_emails_movement_information",
          "label": "Recevoir les e-mails nationaux"
        },
        {
          "code": "subscribed_emails_weekly_letter",
          "label": "Recevoir la newsletter hebdomadaire nationale"
        },
        {
          "code": "candidate_email",
          "label": "Recevoir les e-mails de mes candidat(e)s LaREM"
        },
        {
          "code": "deputy_email",
          "label": "Recevoir les e-mails de mon/ma député(e)"
        },
        {
          "code": "subscribed_emails_local_host",
          "label": "Recevoir les e-mails de mon animateur(trice) local(e) de comité"
        },
        {
          "code": "subscribed_emails_referents",
          "label": "Recevoir les e-mails de mon/ma référent(e) territorial(e)"
        },
        {
          "code": "senator_email",
          "label": "Recevoir les e-mails de mon/ma sénateur/trice"
        }
      ],
      "positions": [
        {
          "code": "student",
          "label": "Étudiant"
        },
        {
          "code": "retired",
          "label": "Retraité"
        },
        {
          "code": "employed",
          "label": "En activité"
        },
        {
          "code": "unemployed",
          "label": "En recherche d'emploi"
        },
        {
          "code": "self_employed_and_liberal_professions",
          "label": "Indépendant / Profession libérale"
        },
        {
          "code": "worker",
          "label": "Ouvrier"
        },
        {
          "code": "intermediate_profession",
          "label": "Profession intermédiaire"
        },
        {
          "code": "executive",
          "label": "Cadre"
        }
      ],
      "jobs": [
        "Agents administratifs",
        "Agents commerciaux",
        "Agents de service, de sécurité, d'accueil ou de surveillance",
        "Agriculteurs, pêcheurs, horticulteurs, éleveurs, chasseurs",
        "Artisans",
        "Auxiliaires médicaux et ambulanciers (aides soignants, opticiens, infirmiers, etc.)",
        "Cadres administratifs",
        "Cadres commerciaux",
        "Cadres techniques",
        "Chargés de clientèle",
        "Chargés de mission",
        "Chefs d'entreprise de 10 à 49 salariés",
        "Chefs d'entreprise de 50 à 499 salariés",
        "Chefs d'entreprise de 500 salariés et plus",
        "Chefs d'équipe 10 à 49 salariés",
        "Chefs d'équipe de 50 à 499 salariés",
        "Chefs d'équipe de 500 salariés et plus",
        "Chefs de produits",
        "Chefs de projets",
        "Chercheurs, chargés de recherche ou d'études",
        "Clergé, religieux",
        "Commerçants",
        "Conseillers",
        "Directeurs de structures de 10 à 49 salariés",
        "Directeurs de structures de 50 à 499 salariés",
        "Directeurs de structures de plus de 500 salariés",
        "Employés",
        "Enseignants, professeurs, instituteurs, inspecteurs",
        "Entrepreneurs",
        "Experts comptables, comptables agréés, libéraux",
        "Exploitants",
        "Formateurs, animateurs, éducateurs, moniteurs",
        "Gestionnaires d'établissements privés (enseignement, santé, social)",
        "Ingénieurs",
        "Métiers artistiques et créatifs (photographes, auteurs, etc.)",
        "Métiers de l'aménagement et de l'urbanisme (géomètres, architectes, etc.)",
        "Métiers du bâtiment et des travaux publics (maçons, électriciens, couvreurs, plombiers, chauffagistes, peintres, etc.)",
        "Métiers du journalisme et de la communication",
        "Ouvriers",
        "Personnes exerçant un mandat politique ou syndical",
        "Pharmaciens, préparateurs en pharmacie",
        "Policiers et militaires",
        "Professions intermédiaires techniques et commerciales",
        "Professions juridiques (avocats, magistrats, notaires, huissiers de justice, etc.)",
        "Professions médicales (médecins, sages-femmes, chirurgiens-dentistes)",
        "Psychologues, psychanalystes, psychothérapeutes",
        "Techniciens",
        "Vétérinaires"
      ],
      "activity_area": [
        "Agriculture, chasse, pêche, élevage, sylviculture et horticulture",
        "Alimentation",
        "Architecture et aménagement",
        "Artisanat",
        "Bâtiment et travaux publics",
        "Biologie et chimie",
        "Commerce et immobilier",
        "Culture, arts et spectacle",
        "Droit",
        "Édition, imprimerie et livres",
        "Energie, gestion des ressources naturelles et des déchets",
        "Enseignement, recherche et formation",
        "Environnement, nature et nettoyage",
        "Finance, banque et assurance",
        "Gestion, audit et ressources humaines",
        "Industrie",
        "Information, communication et audiovisuel",
        "Informatique et électronique",
        "Santé",
        "Sciences et physique",
        "Sciences sociales et politique",
        "Sécurité, défense et secours",
        "Social et humanitaire",
        "Sois, esthétique et coiffure",
        "Sport et animation",
        "Tourisme, hotellerie et restauration",
        "Transports, logistique, aéronautique"
      ]
    }
    """
