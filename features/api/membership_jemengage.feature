@api
@renaissance
Feature:
    In order to create a JeMengage user
    As a non logged-in user
    I should be able to access API membership

    Scenario: As a non logged-in user I cannot create a JeMengage user with empty and wrong data
        Given I send a "POST" request to "/api/membership?source=jemengage" with body:
            """
            {
                "phone": "0"
            }
            """
        Then the response status code should be 400
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
                "status": "error",
                "message": "Validation Failed",
                "violations": [
                    {
                        "propertyPath": "last_name",
                        "message": "Cette valeur ne doit pas être vide."
                    },
                    {
                        "propertyPath": "gender",
                        "message": "Veuillez renseigner une civilité."
                    },
                    {
                        "propertyPath": "birthdate",
                        "message": "Vous devez spécifier votre date de naissance."
                    },
                    {
                        "propertyPath": "phone",
                        "message": "Cette valeur n'est pas un numéro de téléphone valide."
                    },
                    {
                        "propertyPath": "email_address",
                        "message": "Cette valeur ne doit pas être vide."
                    },
                    {
                        "propertyPath": "cgu_accepted",
                        "message": "Vous devez accepter les conditions générales d'utilisation."
                    }
                ]
            }
            """

    Scenario: As a non logged-in user I can create a JeMengage user
        Given I send a "POST" request to "/api/membership?source=jemengage" with body:
            """
            {
                "email_address": "new-user@en-marche-dev.fr",
                "first_name": "Jules",
                "last_name": "Fullstack",
                "gender": "male",
                "birthdate": "1975-01-01",
                "nationality": "FR",
                "phone": "0611223344",
                "address": {
                    "address": "6 rue neyret",
                    "postal_code": "69001",
                    "city_name": "lyon 1er",
                    "country": "FR"
                },
                "cgu_accepted": true,
                "allow_mobile_notifications": true,
                "allow_email_notifications": true
            }
            """
        Then the response status code should be 201

    Scenario: As a non logged-in user I can request a reset password
        Given I send a "POST" request to "/api/membership/forgot-password?source=jemengage" with body:
            """
            { "email_address": "je-mengage-user-1@en-marche-dev.fr" }
            """
        Then the response status code should be 200
        And I should have 1 email "RenaissanceResetPasswordMessage" for "je-mengage-user-1@en-marche-dev.fr" with payload:
            """
            {
                "template_name": "renaissance-reset-password",
                "template_content": [],
                "message": {
                    "subject": "Réinitialisation de votre mot de passe",
                    "from_email": "ne-pas-repondre@parti-renaissance.fr",
                    "html": null,
                    "global_merge_vars": [
                        {
                            "name": "first_name",
                            "content": "Jules"
                        },
                        {
                            "name": "reset_link",
                            "content": "http://test.renaissance.code/changer-mot-de-passe/@string@/@string@"
                        }
                    ],
                    "from_name": "Renaissance",
                    "to": [
                        {
                            "email": "je-mengage-user-1@en-marche-dev.fr",
                            "type": "to",
                            "name": "Jules Fullstack"
                        }
                    ]
                }
            }
            """
