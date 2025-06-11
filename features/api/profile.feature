@api
@renaissance
Feature:
    As a logged-in user
    I should be able to retrieve and edit my profile information

    Scenario: As a non logged-in user I cannot get my profile information
        When I send a "GET" request to "/api/v3/profile/me"
        Then the response status code should be 401

    Scenario: As a logged-in user I can retrieve and update my profile information
        Given I am logged with "carl999@example.fr" via OAuth client "JeMengage Mobile" with scopes "read:profile write:profile"
        When I send a "GET" request to "/api/v3/profile/me"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
                "id": "@string@-@string@",
                "email_address": "carl999@example.fr",
                "first_name": "Carl",
                "last_name": "Mirabeau",
                "gender": "male",
                "custom_gender": null,
                "nationality": "FR",
                "post_address": {
                    "address": "826 avenue du lys",
                    "postal_code": "77190",
                    "city": "77190-77152",
                    "city_name": "Dammarie-les-Lys",
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
                "main_zone": {
                    "uuid": "@uuid@",
                    "type": "department",
                    "code": "77",
                    "name": "Seine-et-Marne"
                },
                "activity_area": null,
                "subscription_types": [
                    {
                        "label": "Recevoir les emails du national",
                        "code": "subscribed_emails_movement_information"
                    },
                    {
                        "label": "Recevoir la newsletter hebdomadaire nationale",
                        "code": "subscribed_emails_weekly_letter"
                    },
                    {
                        "label": "Recevoir les emails de mon Assemblée départementale",
                        "code": "subscribed_emails_referents"
                    },
                    {
                        "label": "Recevoir les emails de ma/mon député(e) ou de ma/mon délégué(e) de circonscription",
                        "code": "deputy_email"
                    },
                    {
                        "label": "Recevoir les emails de ma/mon sénateur/trice",
                        "code": "senator_email"
                    },
                    {
                        "label": "Recevoir les emails des candidats du parti",
                        "code": "candidate_email"
                    },
                    {
                        "label": "Recevoir les emails d'événements",
                        "code": "event_email"
                    }
                ],
                "interests": [],
                "first_membership_donation": null,
                "last_membership_donation": null,
                "committee_membership": {
                    "committee": {
                        "description": "Le comité « En Marche ! » des habitants du 8ème arrondissement de Paris.",
                        "name": "En Marche Paris 8",
                        "uuid": "@uuid@"
                    },
                    "subscription_date": "@string@.isDateTime()",
                    "uuid": "@uuid@"
                },
                "party_membership": "exclusive",
                "other_party_membership": false,
                "image_url": null,
                "change_email_token": null
            }
            """

        # Update post address
        When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
            """
            {
                "post_address": {
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
                        "propertyPath": "post_address",
                        "message": "Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte."
                    },
                    {
                        "propertyPath": "post_address",
                        "message": "Cette valeur n'est pas un code postal français valide."
                    }
                ]
            }
            """
        When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
            """
            {
                "post_address": {
                    "address": "50 rue de la villette",
                    "postal_code": "69003",
                    "city_name": "Lyon 3ème",
                    "country": "FR"
                },
                "subscription_types": ["subscribed_emails_weekly_letter"],
                "party_membership": "modem",
                "other_party_membership": false
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
                    "city_name": "Lyon 3ème",
                    "country": "FR"
                },
                "subscription_types": [
                    {
                        "label": "Recevoir la newsletter hebdomadaire nationale",
                        "code": "subscribed_emails_weekly_letter"
                    }
                ],
                "party_membership": "modem",
                "other_party_membership": false
            }
            """

        # Address property backward compatibility
        When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
            """
            {
                "address": {
                    "address": "92 bld victor hugo",
                    "postal_code": "92110",
                    "city_name": "Clichy",
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
                    "address": "92 bld victor hugo",
                    "postal_code": "92110",
                    "city": "92110-92024",
                    "city_name": "Clichy",
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
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "gender",
                        "message": "Cette civilité n'est pas valide."
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
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "first_name",
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
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "first_name",
                        "message": "Votre prénom ne peut pas dépasser 50 caractères."
                    },
                    {
                        "propertyPath": "last_name",
                        "message": "Votre nom ne peut pas dépasser 50 caractères."
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
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "first_name",
                        "message": "Votre prénom doit comporter au moins 2 caractères."
                    },
                    {
                        "propertyPath": "first_name",
                        "message": "Cette valeur ne doit pas être vide."
                    },
                    {
                        "propertyPath": "last_name",
                        "message": "Votre nom doit comporter au moins 1 caractères."
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
                "interests": ["unknown_interest_code"]
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
                "interests": ["egalite", "numerique"]
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
        When I send a "PUT" request to "/api/v3/profile/e6977a4d-2646-5f6c-9c82-88e58dca8458" with body:
            """
            {
                "subscription_types": ["militant_action_sms", "subscribed_emails_movement_information"]
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
                        "label": "Recevoir les emails du national",
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
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
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
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
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
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
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
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
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
        And the JSON should be equal to:
            """
            {
                "message": "Validation Failed",
                "status": "error",
                "violations": [
                    {
                        "propertyPath": "email_address",
                        "message": "Ceci n'est pas une adresse email valide."
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
        And I should have 1 email "RenaissanceAdherentChangeEmailMessage" for "new.mail@example.com" with payload:
            """
            {
                "template_name": "renaissance-adherent-change-email",
                "template_content": [],
                "message": {
                    "subject": "Validez votre nouvelle adresse email",
                    "from_email": "ne-pas-repondre@parti-renaissance.fr",
                    "html": null,
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
                    "from_name": "Renaissance",
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
        And the JSON should be equal to:
            """
            {
                "uuid": "313bd28f-efc8-57c9-8ab7-2106c8be9699",
                "id": "@string@-@string@",
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
                "facebook_page_url": null,
                "twitter_page_url": null,
                "linkedin_page_url": null,
                "telegram_page_url": null,
                "job": null,
                "main_zone": null,
                "activity_area": null,
                "nationality": null,
                "post_address": {
                    "address": "",
                    "postal_code": "8057",
                    "city": null,
                    "city_name": null,
                    "country": "CH",
                    "region": null
                },
                "interests": [],
                "first_membership_donation": null,
                "last_membership_donation": null,
                "committee_membership": null,
                "party_membership": "exclusive",
                "other_party_membership": false,
                "image_url": null,
                "change_email_token": null
            }
            """

    Scenario: As a logged-in user I can retrieve the profile available configuration
        Given I am logged with "carl999@example.fr" via OAuth client "Coalition App" with scopes "write:profile"
        When I send a "GET" request to "/api/v3/profile/configuration"
        Then the response status code should be 200
        And the response should be in JSON
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
                        "label": "Recevoir les emails du national"
                    },
                    {
                        "code": "subscribed_emails_weekly_letter",
                        "label": "Recevoir la newsletter hebdomadaire nationale"
                    },
                    {
                        "code": "subscribed_emails_referents",
                        "label": "Recevoir les emails de mon Assemblée départementale"
                    },
                    {
                        "code": "deputy_email",
                        "label": "Recevoir les emails de ma/mon député(e) ou de ma/mon délégué(e) de circonscription"
                    },
                    {
                        "code": "subscribed_emails_local_host",
                        "label": "Recevoir les emails de mon Comité local"
                    },
                    {
                        "code": "senator_email",
                        "label": "Recevoir les emails de ma/mon sénateur/trice"
                    },
                    {
                        "code": "candidate_email",
                        "label": "Recevoir les emails des candidats du parti"
                    },
                    {
                        "code": "jam_email",
                        "label": "Recevoir les emails des Jeunes avec Macron"
                    },
                    {
                        "label": "Recevoir les emails d'événements",
                        "code": "event_email"
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

    Scenario: As a logged-in user I can retrieve my donations and cancel my subscriptions
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scopes "read:profile"
        When I send a "GET" request to "/api/v3/profile/me/donations"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "uuid": "@uuid@",
                    "date": "@string@.isDateTime()",
                    "type": "@string@.oneOf(
                        contains('cb'),
                        contains('check'),
                        contains('transfer'),
                        contains('tpe')
                    )",
                    "type_label": "@string@.oneOf(
                        contains('Carte bleue'),
                        contains('Chèque'),
                        contains('Virement'),
                        contains('TPE')
                    )",
                    "subscription": "@boolean@",
                    "membership": "@boolean@",
                    "status": "@string@.oneOf(
                        contains('waiting_confirmation'),
                        contains('subscription_in_progress'),
                        contains('refunded'),
                        contains('canceled'),
                        contains('finished'),
                        contains('error')
                    )",
                    "amount": "@number@"
                },
                "@array_previous_repeat@"
            ]
            """
        And the JSON should be equal to:
            """
            [
                {
                    "uuid": "@uuid@",
                    "date": "@string@.isDateTime()",
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": false,
                    "membership": true,
                    "status": "finished",
                    "amount": 30
                },
                {
                    "uuid": "@uuid@",
                    "date": "@string@.isDateTime()",
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": false,
                    "membership": true,
                    "status": "finished",
                    "amount": 30
                },
                {
                    "uuid": "@uuid@",
                    "date": "@string@.isDateTime()",
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": true,
                    "membership": false,
                    "status": "subscription_in_progress",
                    "amount": 42
                },
                {
                    "uuid": "@uuid@",
                    "date": "@string@.isDateTime()",
                    "type": "cb",
                    "type_label": "Carte bleue",
                    "subscription": false,
                    "membership": false,
                    "status": "finished",
                    "amount": 50
                },
                {
                    "uuid": "@uuid@",
                    "date": "@string@.isDateTime()",
                    "type": "check",
                    "type_label": "Chèque",
                    "subscription": false,
                    "membership": false,
                    "status": "waiting_confirmation",
                    "amount": 30
                }
            ]
            """

        Then I send a "POST" request to "/api/v3/profile/me/donations/cancel"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "OK"
            """

    Scenario: As a logged-in user I can retrieve my tax receipts
        Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Mobile" with scopes "read:profile"
        When I send a "GET" request to "/api/v3/profile/me/tax_receipts"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "label": "@string@.pdf",
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()"
                },
                {
                    "label": "@string@.pdf",
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()"
                },
                {
                    "label": "@string@.pdf",
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()"
                },
                {
                    "label": "@string@.pdf",
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()"
                },
                {
                    "label": "@string@.pdf",
                    "uuid": "@uuid@",
                    "created_at": "@string@.isDateTime()"
                }
            ]
            """

    Scenario Outline: As a logged-in user I can retrieve my certification details
        Given I am logged with "<email>" via OAuth client "JeMengage Mobile" with scopes "read:profile"
        When I send a "GET" request to "/api/v3/profile/me/certification-request"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "certified_at": "@string@.isDateTime()||@null@",
                "last_certification_request": {
                    "created_at": "@string@.isDateTime()",
                    "status": "@string@.oneOf(
                        contains('pending'),
                        contains('approved'),
                        contains('refused'),
                        contains('blocked')
                    )",
                    "processed_at": "@string@.isDateTime()||@null@",
                    "refusal_reason": "@string@.oneOf(
                        contains('document_not_in_conformity'),
                        contains('document_not_readable'),
                        contains('informations_not_matching'),
                        contains('process_timeout'),
                        contains('birth_date_not_matching'),
                        contains('unreadable_document'),
                        contains('document_not_original'),
                        contains('reversed_first_and_last_name'),
                        contains('document_not_fully_visible'),
                        contains('document_not_front'),
                        contains('partial_first_name'),
                        contains('other')
                    )||@null@",
                    "custom_refusal_reason": "@string@||@null@"
                }
            }
            """

        Examples:
            | email                        |
            | luciole1989@spambox.fr       |
            | carl999@example.fr           |
            | gisele-berthoux@caramail.com |

    Scenario: I can retrieve committees list for my zone and select my new committee
        Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "VOX" with scope "jemarche_app read:profile write:profile"
        When I send a "GET" request to "/api/v3/profile/me"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "gender": "female",
                "custom_gender": null,
                "email_address": "gisele-berthoux@caramail.com",
                "phone": {
                    "country": "FR",
                    "number": "01 38 76 43 34"
                },
                "birthdate": "1983-12-24T00:00:00+01:00",
                "position": "unemployed",
                "subscription_types": [
                    {
                        "label": "Recevoir les informations sur les actions militantes du mouvement par téléphone",
                        "code": "militant_action_sms"
                    },
                    {
                        "label": "Recevoir les emails du national",
                        "code": "subscribed_emails_movement_information"
                    },
                    {
                        "label": "Recevoir la newsletter hebdomadaire nationale",
                        "code": "subscribed_emails_weekly_letter"
                    },
                    {
                        "label": "Recevoir les emails de ma/mon député(e) ou de ma/mon délégué(e) de circonscription",
                        "code": "deputy_email"
                    },
                    {
                        "label": "Recevoir les emails de mon Comité local",
                        "code": "subscribed_emails_local_host"
                    },
                    {
                        "label": "Recevoir les emails de ma/mon sénateur/trice",
                        "code": "senator_email"
                    },
                    {
                        "label": "Recevoir les emails d'événements",
                        "code": "event_email"
                    }
                ],
                "facebook_page_url": null,
                "twitter_page_url": null,
                "linkedin_page_url": null,
                "telegram_page_url": null,
                "job": null,
                "main_zone": {
                    "uuid": "@uuid@",
                    "type": "department",
                    "code": "92",
                    "name": "Hauts-de-Seine"
                },
                "activity_area": null,
                "nationality": "FR",
                "post_address": {
                    "address": "47 rue Martre",
                    "postal_code": "92110",
                    "city": "92110-92024",
                    "city_name": "Clichy",
                    "country": "FR",
                    "region": null
                },
                "first_membership_donation": "@string@.isDateTime()",
                "last_membership_donation": "@string@.isDateTime()",
                "party_membership": "exclusive",
                "other_party_membership": false,
                "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                "id": "@string@-@string@",
                "first_name": "Gisele",
                "last_name": "Berthoux",
                "committee_membership": {
                    "committee": {
                        "description": "Un petit comité avec seulement 3 communes",
                        "uuid": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3",
                        "name": "Second Comité des 3 communes"
                    },
                    "uuid": "@uuid@",
                    "subscription_date": "@string@.isDateTime()"
                },
                "certified": false,
                "interests": [],
                "image_url": null,
                "change_email_token": null
            }
            """
        When I send a "GET" request to "/api/v3/profile/committees"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            [
                {
                    "members_count": 10,
                    "adherents_count": 0,
                    "sympathizers_count": 0,
                    "members_em_count": 0,
                    "description": "Un petit comité avec seulement 3 communes",
                    "uuid": "5e00c264-1d4b-43b8-862e-29edc38389b3",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Comité des 3 communes",
                    "animator": {
                        "uuid": "@uuid@",
                        "id": "@string@",
                        "first_name": "Adherent 55",
                        "last_name": "Fa55ke",
                        "image_url": null,
                        "role": "Responsable comité local"
                    }
                },
                {
                    "members_count": 3,
                    "adherents_count": 0,
                    "sympathizers_count": 0,
                    "members_em_count": 0,
                    "description": "Un petit comité avec seulement 3 communes",
                    "uuid": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3",
                    "created_at": "@string@.isDateTime()",
                    "updated_at": "@string@.isDateTime()",
                    "name": "Second Comité des 3 communes",
                    "animator": {
                        "uuid": "@uuid@",
                        "id": "@string@",
                        "first_name": "Adherent 56",
                        "last_name": "Fa56ke",
                        "image_url": null,
                        "role": "Responsable comité local"
                    }
                }
            ]
            """
        When I send a "PUT" request to "/api/v3/profile/committees/5e00c264-1d4b-43b8-862e-29edc38389b3/join"
        Then the response status code should be 200
        When I send a "GET" request to "/api/v3/profile/me"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "gender": "female",
                "custom_gender": null,
                "email_address": "gisele-berthoux@caramail.com",
                "phone": {
                    "country": "FR",
                    "number": "01 38 76 43 34"
                },
                "birthdate": "1983-12-24T00:00:00+01:00",
                "position": "unemployed",
                "subscription_types": [
                    {
                        "label": "Recevoir les informations sur les actions militantes du mouvement par téléphone",
                        "code": "militant_action_sms"
                    },
                    {
                        "label": "Recevoir les emails du national",
                        "code": "subscribed_emails_movement_information"
                    },
                    {
                        "label": "Recevoir la newsletter hebdomadaire nationale",
                        "code": "subscribed_emails_weekly_letter"
                    },
                    {
                        "label": "Recevoir les emails de ma/mon député(e) ou de ma/mon délégué(e) de circonscription",
                        "code": "deputy_email"
                    },
                    {
                        "label": "Recevoir les emails de mon Comité local",
                        "code": "subscribed_emails_local_host"
                    },
                    {
                        "label": "Recevoir les emails de ma/mon sénateur/trice",
                        "code": "senator_email"
                    },
                    {
                        "label": "Recevoir les emails d'événements",
                        "code": "event_email"
                    }
                ],
                "facebook_page_url": null,
                "twitter_page_url": null,
                "linkedin_page_url": null,
                "telegram_page_url": null,
                "job": null,
                "main_zone": {
                    "uuid": "@uuid@",
                    "type": "department",
                    "code": "92",
                    "name": "Hauts-de-Seine"
                },
                "activity_area": null,
                "nationality": "FR",
                "post_address": {
                    "address": "47 rue Martre",
                    "postal_code": "92110",
                    "city": "92110-92024",
                    "city_name": "Clichy",
                    "country": "FR",
                    "region": null
                },
                "first_membership_donation": "@string@.isDateTime()",
                "last_membership_donation": "@string@.isDateTime()",
                "party_membership": "exclusive",
                "other_party_membership": false,
                "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                "id": "@string@-@string@",
                "first_name": "Gisele",
                "last_name": "Berthoux",
                "committee_membership": {
                    "committee": {
                        "description": "Un petit comité avec seulement 3 communes",
                        "uuid": "5e00c264-1d4b-43b8-862e-29edc38389b3",
                        "name": "Comité des 3 communes"
                    },
                    "uuid": "@uuid@",
                    "subscription_date": "@string@.isDateTime()"
                },
                "certified": false,
                "interests": [],
                "image_url": null,
                "change_email_token": null
            }
            """

    Scenario: I can update my profile image
        Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "VOX" with scope "jemarche_app read:profile write:profile"
        When I send a "GET" request to "/api/v3/profile/me"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "gender": "female",
                "custom_gender": null,
                "email_address": "gisele-berthoux@caramail.com",
                "phone": {
                    "country": "FR",
                    "number": "01 38 76 43 34"
                },
                "birthdate": "1983-12-24T00:00:00+01:00",
                "position": "unemployed",
                "subscription_types": [
                    {
                        "label": "Recevoir les informations sur les actions militantes du mouvement par téléphone",
                        "code": "militant_action_sms"
                    },
                    {
                        "label": "Recevoir les emails du national",
                        "code": "subscribed_emails_movement_information"
                    },
                    {
                        "label": "Recevoir la newsletter hebdomadaire nationale",
                        "code": "subscribed_emails_weekly_letter"
                    },
                    {
                        "label": "Recevoir les emails de ma/mon député(e) ou de ma/mon délégué(e) de circonscription",
                        "code": "deputy_email"
                    },
                    {
                        "label": "Recevoir les emails de mon Comité local",
                        "code": "subscribed_emails_local_host"
                    },
                    {
                        "label": "Recevoir les emails de ma/mon sénateur/trice",
                        "code": "senator_email"
                    },
                    {
                        "label": "Recevoir les emails d'événements",
                        "code": "event_email"
                    }
                ],
                "facebook_page_url": null,
                "twitter_page_url": null,
                "linkedin_page_url": null,
                "telegram_page_url": null,
                "job": null,
                "main_zone": {
                    "uuid": "@uuid@",
                    "type": "department",
                    "code": "92",
                    "name": "Hauts-de-Seine"
                },
                "activity_area": null,
                "nationality": "FR",
                "post_address": {
                    "address": "47 rue Martre",
                    "postal_code": "92110",
                    "city": "92110-92024",
                    "city_name": "Clichy",
                    "country": "FR",
                    "region": null
                },
                "first_membership_donation": "@string@.isDateTime()",
                "last_membership_donation": "@string@.isDateTime()",
                "party_membership": "exclusive",
                "other_party_membership": false,
                "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
                "id": "@string@-@string@",
                "first_name": "Gisele",
                "last_name": "Berthoux",
                "committee_membership": {
                    "committee": {
                        "description": "Un petit comité avec seulement 3 communes",
                        "uuid": "8c4b48ec-9290-47ae-a5db-d1cf2723e8b3",
                        "name": "Second Comité des 3 communes"
                    },
                    "uuid": "@uuid@",
                    "subscription_date": "@string@.isDateTime()"
                },
                "certified": false,
                "interests": [],
                "image_url": null,
                "change_email_token": null
            }
            """
        When I send a "POST" request to "/api/v3/profile/b4219d47-3138-5efd-9762-2ef9f9495084/image" with body:
            """
            {
                "content": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAFCAYAAACNbyblAAAAHElEQVQI12P4//8/w38GIAXDIBKE0DHxgljNBAAO9TXL0Y4OHwAAAABJRU5ErkJggg=="
            }
            """
        Then the response status code should be 200
        When I send a "GET" request to "/api/v3/profile/me"
        Then the JSON should be a superset of:
            """
            { "image_url": "http://test.renaissance.code/assets/images/profile/@string@.png" }
            """
        When I send a "DELETE" request to "/api/v3/profile/b4219d47-3138-5efd-9762-2ef9f9495084/image"
        Then the response status code should be 200
        When I send a "GET" request to "/api/v3/profile/me"
        Then the JSON should be a superset of:
            """
            { "image_url": null }
            """

    Scenario Outline: As a logged-in user I can not change my password with a weak password
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scopes "write:profile"
        When I send a "POST" request to "/api/v3/profile/me/password-change" with body:
            """
            {
                "old_password": "secret!12345",
                "new_password": "<password>",
                "new_password_confirmation": "<password>"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "violations": [
                    {
                        "propertyPath": "new_password",
                        "message": "<error_message>"
                    }
                ]
            }
            """

        Examples:
            | password     | error_message                                                                                     |
            | newpassword@ | Le mot de passe doit contenir au moins une lettre majuscule.                                      |
            | NEWPASSWORD@ | Le mot de passe doit contenir au moins une lettre minuscule.                                      |
            | NewPassword  | Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*()-_=+{}\|:;\"'<>,.?[]\\\/). |
            | New@         | Le mot de passe doit faire au moins 8 caractères.                                                 |

    Scenario: As a logged-in user I can not change my password if new passwords do not match
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scopes "write:profile"
        When I send a "POST" request to "/api/v3/profile/me/password-change" with body:
            """
            {
                "old_password": "secret!12345",
                "new_password": "NewPassword@",
                "new_password_confirmation": "NewPassword@2"
            }
            """
        Then the response status code should be 400
        And the JSON should be a superset of:
            """
            {
                "violations": [
                    {
                        "propertyPath": "new_password",
                        "message": "Les mots de passe ne correspondent pas."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can change my password
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scopes "write:profile"
        When I send a "POST" request to "/api/v3/profile/me/password-change" with body:
            """
            {
                "old_password": "secret!12345",
                "new_password": "NewPassword@",
                "new_password_confirmation": "NewPassword@"
            }
            """
        Then the response status code should be 200

    Scenario: As a logged-in user I can not unregister with invalid reason
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile"
        When I send a "POST" request to "/api/v3/profile/unregister" with body:
            """
            {
                "reasons": ["lorem ipsum"]
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            {
                "violations": [
                    {
                        "propertyPath": "reasons",
                        "message": "Une ou plusieurs des valeurs soumises sont invalides."
                    }
                ]
            }
            """

    Scenario: As a logged-in user I can unregister
        Given I am logged with "benjyd@aol.com" via OAuth client "JeMengage Mobile"
        When I send a "POST" request to "/api/v3/profile/unregister" with body:
            """
            {
                "reasons": ["unregistration_reasons.emails", "unregistration_reasons.tools"],
                "comment": "Lorem ipsum dolor sit amet"
            }
            """
        Then the response status code should be 200

    Scenario: As a logged-in user I can unregister with empty body
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile"
        And I add "X-App-Version" header equal to "v5.10.0"
        When I send a "POST" request to "/api/v3/profile/unregister"
        Then the response status code should be 200

    Scenario: As a logged-in user I can unregister with empty body
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile"
        And I add "X-App-Version" header equal to "v5.9.0#10"
        When I send a "POST" request to "/api/v3/profile/unregister"
        Then the response status code should be 200

    Scenario: As a logged-in user with new app version I can not unregister with empty body
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile"
        And I add "X-App-Version" header equal to "v5.18.0#0"
        When I send a "POST" request to "/api/v3/profile/unregister"
        Then the response status code should be 400

    Scenario: As a logged-in user I can get the list of my instances
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scopes "read:profile"
        When I send a "GET" request to "/api/v3/profile/instances"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "type": "assembly",
                    "code": "FDE",
                    "name": "Français de l'Étranger",
                    "manager": null
                },
                {
                    "type": "committee",
                    "uuid": null,
                    "name": null,
                    "members_count": null,
                    "assembly_committees_count": 8,
                    "can_change_committee": true,
                    "message": null,
                    "manager": null
                }
            ]
            """
        When I am logged with "gisele-berthoux@caramail.com" via OAuth client "JeMengage Mobile" with scopes "read:profile"
        And I send a "GET" request to "/api/v3/profile/instances"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "type": "assembly",
                    "code": "92",
                    "name": "Hauts-de-Seine (92)",
                    "manager": {
                        "uuid": "@uuid@",
                        "public_id": "@string@",
                        "first_name": "Referent",
                        "last_name": "Referent",
                        "image_url": null,
                        "role": "Président d'assemblée départementale"
                    }
                },
                {
                    "type": "circonscription",
                    "code": "92-4",
                    "name": "4ème circonscription • Hauts-de-Seine (92-4)",
                    "manager": null
                },
                {
                    "type": "committee",
                    "uuid": "@uuid@",
                    "name": "Second Comité des 3 communes",
                    "members_count": 3,
                    "assembly_committees_count": 2,
                    "can_change_committee": true,
                    "message": null,
                    "manager": {
                        "uuid": "@uuid@",
                        "public_id": "@string@",
                        "first_name": "Adherent 56",
                        "last_name": "Fa56ke",
                        "image_url": null,
                        "role": "Responsable comité local"
                    }
                },
                {
                    "type": "agora",
                    "uuid": "@uuid@",
                    "name": "Deuxième Agora",
                    "slug": "deuxieme-agora",
                    "description": "Description deuxième Agora",
                    "max_members_count": 40,
                    "members_count": 1,
                    "manager": {
                        "uuid": "@uuid@",
                        "public_id": "@string@",
                        "first_name": "Jacques",
                        "last_name": "Picard",
                        "image_url": null,
                        "role": "Président d'Agora"
                    }
                }
            ]
            """
