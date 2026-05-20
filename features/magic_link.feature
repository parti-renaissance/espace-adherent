@app
@renaissance_user
Feature:
    In order to log in without a password
    As a Renaissance user
    I should receive a magic link email sent with the sender configured on the email template

    Scenario: The magic link email uses the sender inherited from the parent template
        Given I am on "/demander-un-lien-magique"
        When I fill in "email" with "luciole1989@spambox.fr"
        And I press "M’envoyer un lien"
        Then I should have 1 email "RenaissanceMagicLinkMessage" for "luciole1989@spambox.fr" with payload:
            """
            {
                "template_name": "",
                "template_content": [],
                "message": {
                    "subject": "Votre lien de connexion",
                    "from_email": "ne-pas-repondre@renaissance.code",
                    "html": "@string@",
                    "merge_vars": [
                        {
                            "rcpt": "luciole1989@spambox.fr",
                            "vars": [
                                {
                                    "name": "magic_link",
                                    "content": "@string@.isUrl()"
                                }
                            ]
                        }
                    ],
                    "from_name": "Ne pas répondre",
                    "to": [
                        {
                            "email": "luciole1989@spambox.fr",
                            "type": "to",
                            "name": "@string@"
                        }
                    ]
                }
            }
            """
