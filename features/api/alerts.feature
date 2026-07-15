@api
@renaissance_api
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
                    "type": "alert",
                    "label": "Alerte fin demain",
                    "title": "Une alerte de test se termine demain !",
                    "description": "Alerte temporaire pour vérifier le tri par date de fin.",
                    "cta_label": "Tester",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=gisele-berthoux@caramail.com&expires=@string@&hash=@string@&_failure_path=%2Fconnexion&_target_path=%2Fconsultations%2Ffin-demain",
                    "image_url": null,
                    "data": null,
                    "share_url": null
                },
                {
                    "type": "poll",
                    "label": "Sondage",
                    "title": "Plutôt thé ou café ?",
                    "description": "",
                    "cta_label": "Je participe",
                    "cta_url": "/sondage/8adca369-938c-450b-92e9-9c2b1f206fa3",
                    "image_url": null,
                    "data": {
                        "uuid": "8adca369-938c-450b-92e9-9c2b1f206fa3",
                        "question": "Plutôt thé ou café ?",
                        "start_at": "@string@.isDateTime()",
                        "finish_at": "@string@.isDateTime()",
                        "participated": false
                    },
                    "share_url": null
                },
                {
                    "type": "alert",
                    "label": "Alerte fin dans trois jours",
                    "title": "Une alerte de test se termine dans trois jours !",
                    "description": "Alerte temporaire pour vérifier le tri par date de fin.",
                    "cta_label": "Tester",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=gisele-berthoux@caramail.com&expires=@string@&hash=@string@&_failure_path=%2Fconnexion&_target_path=%2Fconsultations%2Ffin-trois-jours",
                    "image_url": null,
                    "data": null,
                    "share_url": null
                },
                {
                    "type": "alert",
                    "label": "Alerte fin dans sept jours",
                    "title": "Une alerte de test se termine dans sept jours !",
                    "description": "Alerte temporaire pour vérifier le tri par date de fin.",
                    "cta_label": "Tester",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=gisele-berthoux@caramail.com&expires=@string@&hash=@string@&_failure_path=%2Fconnexion&_target_path=%2Fconsultations%2Ffin-sept-jours",
                    "image_url": null,
                    "data": null,
                    "share_url": null
                },
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
                    "type": "alert",
                    "label": "Nouvelle consultation",
                    "title": "Une nouvelle consultation est disponible !",
                    "description": "Consultez la nouvelle proposition de loi et donnez votre avis.",
                    "cta_label": "Voir la consultation",
                    "cta_url": "http://test.renaissance.code/connexion-avec-un-lien-magique?user=gisele-berthoux@caramail.com&expires=@string@&hash=@string@&_failure_path=%2Fconnexion&_target_path=%2Fconsultations%2F123",
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
                }
            ]
            """
