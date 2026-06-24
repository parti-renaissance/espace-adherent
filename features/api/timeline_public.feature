@api
@renaissance_api
Feature:
    In order to show a public timeline on the website
    As an anonymous visitor
    I should be able to read public timeline items without authentication

    Scenario: Anyone can read the public timeline without being logged in
        When I send a "GET" request to "/api/timeline-feeds"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "hits" should exist
        And the JSON should be a superset of:
            """
            {
                "page": 0,
                "hitsPerPage": 10
            }
            """
        # The feed is identical for every visitor and carries no per-user data: it is publicly cached
        # (the stateful "main" firewall would otherwise downgrade this to private).
        And the header "Cache-Control" should contain "public"
        And the header "Cache-Control" should contain "max-age=120"

    Scenario: The page query parameter is honoured
        When I send a "GET" request to "/api/timeline-feeds?page=2"
        Then the response status code should be 200
        And the JSON should be a superset of:
            """
            {
                "page": 2
            }
            """
