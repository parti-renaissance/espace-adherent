@api
@renaissance
Feature:

    Scenario: As a logged-in VOX user I can get my alerts
        Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "J'écoute" with scope "jemarche_app"
        And I send a "GET" request to "/api/v3/alerts"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "label": "Consultation / Élection",
                    "title": "Élection en cours !!",
                    "description": "# Élection\nvous avez **5 jours** pour voter.",
                    "cta_label": "Consulter",
                    "cta_url": "http://test.renaissance.code/election-sas/@uuid@"
                }
            ]
            """
