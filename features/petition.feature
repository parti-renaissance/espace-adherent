@app
Feature:

    Scenario: I can sign a petition
        Given I send a "POST" request to "/api/petition/signature" with body:
            """
            {
                "civility": "Madame",
                "first_name": "Gisele",
                "last_name": "Berthoux",
                "phone": null,
                "email": "gisele-berthoux@caramail.com",
                "postal_code": "75008",
                "petition_name": "Ma premiere petition",
                "petition_slug": "ma-premiere-petition",
                "utm_source": "from-email",
                "utm_campaign": "2025-01-29",
                "recaptcha": "fake",
                "cgu_accepted": true,
                "newsletter": false
            }
            """
        Then the response status code should be 201
        And the response should be in JSON
        And the JSON should be equal to:
            """
            "OK"
            """
        And I should have 1 email "PetitionConfirmationMessage" for "gisele-berthoux@caramail.com" with payload:
            """
            {
                "template_name": "petition-confirmation",
                "template_content": [],
                "message": {
                    "subject": "Confirmez votre signature à la pétition",
                    "from_email": "ne-pas-repondre@parti-renaissance.fr",
                    "html": null,
                    "global_merge_vars": [
                        {
                            "name": "first_name",
                            "content": "Gisele"
                        },
                        {
                            "name": "petition_name",
                            "content": "Ma premiere petition"
                        },
                        {
                            "name": "primary_link",
                            "content": "http://test.renaissance.code/petition/validate/@string@/@string@"
                        }
                    ],
                    "from_name": "Renaissance",
                    "to": [
                        {
                            "email": "gisele-berthoux@caramail.com",
                            "type": "to",
                            "name": "Gisele Berthoux"
                        }
                    ]
                }
            }
            """
        When I stop following redirections
        And I click on the email link "primary_link"
        Then the response status code should be 302
        And the header "Location" should be equal to "/petitions/ma-premiere-petition/merci"
