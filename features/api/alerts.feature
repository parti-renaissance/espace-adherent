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
                    "type": "election",
                    "label": "Consultation / Élection",
                    "title": "Élection en cours !!",
                    "description": "L'élection sera ouverte du @string@ au @string@.\n\n# Élection\nvous avez **5 jours** pour voter.",
                    "cta_label": "Voir",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=gisele-berthoux@caramail.com&expires=@string@&hash=@string@&_failure_path=%2Fconnexion&_target_path=%2Felection-sas%2F@uuid@",
                    "image_url": null,
                    "data": null,
                    "share_url": null
                },
                {
                    "type": "election",
                    "label": "Consultation / Élection",
                    "title": "Élection en cours !!",
                    "description": "L'élection sera ouverte du @string@ au @string@.\n\n# Élection\nvous avez **5 jours** pour voter.",
                    "cta_label": "Consulter",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=gisele-berthoux@caramail.com&expires=@string@&hash=@string@&_failure_path=%2Fconnexion&_target_path=%2Felection-sas%2F@uuid@",
                    "image_url": null,
                    "data": null,
                    "share_url": null
                },
                {
                    "type": "meeting",
                    "label": "Grand rassemblement",
                    "title": "Venez nombreux !",
                    "description": "",
                    "cta_label": "Je réserve ma place",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?@string@&_target_path=%2Fgrand-rassemblement%2Fcampus",
                    "image_url": "http://test.renaissance.code/assets/uploads/@string@",
                    "data": null,
                    "share_url": "http://test.renaissance.code/grand-rassemblement/campus/@string@?utm_source=app&utm_campaign=alerte"
                },
                {
                    "type": "meeting",
                    "label": "Grand rassemblement",
                    "title": "Venez nombreux !",
                    "description": "",
                    "cta_label": "Je réserve ma place",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?@string@&_target_path=%2Fgrand-rassemblement%2Fmeeting-nrp",
                    "image_url": "@string@.isUrl()",
                    "data": null,
                    "share_url": "http://test.renaissance.code/grand-rassemblement/meeting-nrp/@string@?utm_source=app&utm_campaign=alerte"
                },
                {
                    "type": "meeting",
                    "label": "Grand rassemblement",
                    "title": "Venez nombreux !",
                    "description": "",
                    "cta_label": "Je réserve ma place",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?@string@&_target_path=%2Fgrand-rassemblement%2Fevent-national-1",
                    "image_url": null,
                    "data": null,
                    "share_url": "http://test.renaissance.code/grand-rassemblement/event-national-1/@string@?utm_source=app&utm_campaign=alerte"
                },
                {
                    "type": "alert",
                    "label": "Nouvelle consultation",
                    "title": "Une nouvelle consultation est disponible !",
                    "description": "Consultez la nouvelle proposition de loi et donnez votre avis.",
                    "cta_label": "Voir la consultation",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=gisele-berthoux@caramail.com&expires=@string@&hash=@string@&_failure_path=%2Fconnexion&_target_path=%2Fconsultations%2F123",
                    "image_url": null,
                    "data": null,
                    "share_url": null
                }
            ]
            """
