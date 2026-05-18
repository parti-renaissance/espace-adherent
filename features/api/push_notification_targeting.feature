@api
@renaissance_api
Feature:
    In order to ensure push notifications reach the correct audience
    As a platform administrator
    I should verify token selection for each notification type

    Scenario: National news targets all active push tokens and excludes dead tokens
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        Then I should have 0 notification
        When I send a "POST" request to "/api/v3/jecoute/news?scope=national" with body:
            """
            {
                "title": "Actualité nationale test push",
                "content": "Contenu test pour vérifier le ciblage national des tokens push.",
                "external_link": "http://test.en-marche.fr",
                "link_label": "Voir",
                "global": true,
                "notification": true,
                "published": true
            }
            """
        Then the response status code should be 201
        And I should have 1 notification "NewsCreatedNotification" with data:
            | key   | value    |
            | scope | national |
        And the notification "NewsCreatedNotification" should target at least 1 push tokens
        And the notification "NewsCreatedNotification" should not include dead tokens

    Scenario: Committee news targets only committee members, not all national tokens
        Given I am logged with "adherent-male-55@en-marche-dev.fr" via OAuth client "JeMengage Web"
        Then I should have 0 notification
        When I send a "POST" request to "/api/v3/jecoute/news?scope=animator" with body:
            """
            {
                "title": "Actualité comité test push",
                "content": "Contenu test pour vérifier le ciblage comité des tokens push.",
                "external_link": "http://test.en-marche.fr",
                "link_label": "Voir",
                "global": true,
                "notification": true,
                "published": true,
                "committee": "5e00c264-1d4b-43b8-862e-29edc38389b3"
            }
            """
        Then the response status code should be 201
        And I should have 1 notification "NewsCreatedNotification" with data:
            | key   | value              |
            | scope | committee:@number@ |
        And the notification "NewsCreatedNotification" should target at least 1 push tokens
        And the notification "NewsCreatedNotification" should target less than 10 push tokens
        And the notification "NewsCreatedNotification" should not include dead tokens

    Scenario: Zone-scoped action targets zone tokens only, not all national
        Given I am logged with "president-ad@renaissance-dev.fr" via OAuth client "J'écoute" with scope "jemarche_app"
        Then I should have 0 notification
        When I send a "POST" request to "/api/v3/actions?scope=president_departmental_assembly" with body:
            """
            {
                "type": "pap",
                "date": "2024-06-01 10:00:00",
                "description": "<p>Test ciblage zone</p>",
                "post_address": {
                    "address": "92 bd Victor Hugo",
                    "postal_code": "92110",
                    "city_name": "Clichy",
                    "country": "FR"
                }
            }
            """
        Then the response status code should be 201
        And I should have 1 notification "ActionCreatedNotification" with data:
            | key   | value         |
            | scope | zone:@string@ |
        And the notification "ActionCreatedNotification" should target at least 1 push tokens
        And the notification "ActionCreatedNotification" should not include dead tokens

    Scenario: Committee event targets strictly less tokens than national news
        Given I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/jecoute/news?scope=national" with body:
            """
            {
                "title": "Actualité nationale référence",
                "content": "Notification nationale servant de référence pour la comparaison.",
                "external_link": "http://test.en-marche.fr",
                "link_label": "Voir",
                "global": true,
                "notification": true,
                "published": true
            }
            """
        Then the response status code should be 201
        Given I am logged with "adherent-male-55@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "POST" request to "/api/v3/events?scope=animator" with body:
            """
            {
                "name": "Événement comité test push",
                "category": "kiosque",
                "description": "Test ciblage comité",
                "begin_at": "2027-06-01 10:00:00",
                "finish_at": "2027-06-01 12:00:00",
                "capacity": 50,
                "committee": "5e00c264-1d4b-43b8-862e-29edc38389b3",
                "post_address": {
                    "address": "92 bd Victor Hugo",
                    "postal_code": "92110",
                    "city_name": "Clichy",
                    "country": "FR"
                }
            }
            """
        Then the response status code should be 201
        And the notification "EventCreatedNotification" should target at least 1 push tokens
        And the notification "EventCreatedNotification" should target strictly less tokens than "NewsCreatedNotification"
        And the notification "EventCreatedNotification" should not include dead tokens
