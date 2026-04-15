@api
@renaissance
Feature:
    In order to display a national events selector in Espace Cadre
    As a logged-in user with the national_event scope feature
    I should be able to list all national events sorted by start_date DESC

    Scenario: As a user granted with national scope, I can list all national events sorted by start_date DESC, excluding JEM events and events older than January 1st of last year
        When I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/national_events?scope=national"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            [
                {
                    "uuid": "@uuid@",
                    "name": "Event national 1",
                    "start_date": "@string@.isDateTime()"
                },
                {
                    "uuid": "@uuid@",
                    "name": "Event national 2",
                    "start_date": "@string@.isDateTime()"
                },
                {
                    "uuid": "@uuid@",
                    "name": "Meeting NRP",
                    "start_date": "@string@.isDateTime()"
                },
                {
                    "uuid": "@uuid@",
                    "name": "Campus",
                    "start_date": "@string@.isDateTime()"
                },
                {
                    "uuid": "@uuid@",
                    "name": "Event passé",
                    "start_date": "@string@.isDateTime()"
                }
            ]
            """

    Scenario: As a user whose scope does not grant the national_event feature, I cannot list national events
        When I am logged with "deputy@en-marche-dev.fr" via OAuth client "JeMengage Web"
        When I send a "GET" request to "/api/v3/national_events?scope=deputy"
        Then the response status code should be 403

    Scenario: As a non logged-in user, I cannot list national events
        When I send a "GET" request to "/api/v3/national_events"
        Then the response status code should be 401
