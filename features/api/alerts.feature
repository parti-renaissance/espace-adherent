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
                    "description": "L'élection sera ouverte du @string@ au @string@.\n\n# Élection\nvous avez **5 jours** pour voter.",
                    "cta_label": "Voir",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=gisele-berthoux@caramail.com&expires=@string@&hash=@string@&_failure_path=%2Fconnexion&_target_path=http%3A%2F%2Ftest.renaissance.code%2Felection-sas%2F@uuid@"
                },
                {
                    "label": "Consultation / Élection",
                    "title": "Élection en cours !!",
                    "description": "L'élection sera ouverte du @string@ au @string@.\n\n# Élection\nvous avez **5 jours** pour voter.",
                    "cta_label": "Consulter",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=gisele-berthoux@caramail.com&expires=@string@&hash=@string@&_failure_path=%2Fconnexion&_target_path=http%3A%2F%2Ftest.renaissance.code%2Felection-sas%2F@uuid@"
                }
            ]
            """
