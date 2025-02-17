@api
@renaissance
Feature:
    As a logged-in user
    I should be able to change my email address

    Scenario: As a logged-in user I can request an email address change
        Given I am logged with "carl999@example.fr" via OAuth client "JeMengage Mobile" with scopes "read:profile write:profile"
        When I send a "GET" request to "/api/v3/profile/me"
        Then the response status code should be 200
        And the JSON should be a superset of:
            """
            {
                "change_email_token": null
            }
            """

        ## No changes
        When I send a "POST" request to "/api/v3/profile/email/request" with body:
            """
            {
                "email_address": "carl999@example.fr"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "message": "Aucun changement d'adresse email."
            }
            """
        And I should have 0 email

        ## Invalid email
        When I send a "POST" request to "/api/v3/profile/email/request" with body:
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
                        "message": "Ceci n'est pas une adresse email valide.",
                        "propertyPath": "email_address"
                    }
                ]
            }
            """
        And I should have 0 email

        ## Successfully request email change
        When I send a "POST" request to "/api/v3/profile/email/request" with body:
            """
            {
                "email_address": "new.mail@example.com"
            }
            """
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "message": "Un mail vous a été envoyé pour confirmer votre changement d'adresse email."
            }
            """
        And I should have 1 email "RenaissanceAdherentChangeEmailMessage" for "new.mail@example.com" with payload:
            """
            {
                "template_name": "renaissance-adherent-change-email",
                "template_content": [],
                "message": {
                    "subject": "Validez votre nouvelle adresse email",
                    "from_email": "ne-pas-repondre@parti-renaissance.fr",
                    "merge_vars": [
                        {
                            "rcpt": "new.mail@example.com",
                            "vars": [
                                {
                                    "name": "first_name",
                                    "content": "Carl"
                                },
                                {
                                    "name": "activation_link",
                                    "content": "@string@.isUrl()"
                                }
                            ]
                        }
                    ],
                    "from_name": "Renaissance",
                    "html": null,
                    "to": [
                        {
                            "email": "new.mail@example.com",
                            "type": "to",
                            "name": "Carl Mirabeau"
                        }
                    ]
                }
            }
            """
        When I send a "GET" request to "/api/v3/profile/me"
        Then the response status code should be 200
        And the JSON should be a superset of:
            """
            {
                "change_email_token": {
                    "uuid": "@uuid@",
                    "email": "new.mail@example.com",
                    "expired_at": "@string@.isDateTime()"
                }
            }
            """

    Scenario: As a logged-in user I can resend a validation for email change
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scopes "read:profile write:profile"
        When I send a "POST" request to "/api/v3/profile/email/send-validation"
        Then the response status code should be 200
        And the JSON should be equal to:
            """
            {
                "message": "Email de validation envoyé avec succès."
            }
            """

    Scenario Outline: As a logged-in user I can not resend a validation for email change for multiple reasons
        Given I am logged with "<email>" via OAuth client "JeMengage Mobile" with scopes "read:profile write:profile"
        When I send a "POST" request to "/api/v3/profile/email/send-validation"
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON node "message" should be equal to "Aucun changement d'adresse email en attente de validation pour cet utilisateur."

        Examples:
            | email                       |
            | carl999@example.fr          |
            | jacques.picard@en-marche.fr |
