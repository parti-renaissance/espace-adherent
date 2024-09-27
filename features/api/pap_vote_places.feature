@api
@renaissance
Feature:
    In order to complete PAP campaigns
    I should be able to retrieve vote places for a given position

    Scenario: As a logged-in user I can retrieve vote places near a given position ordered by distance
        Given I am logged with "michelle.dufour@example.ch" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "GET" request to "/api/v3/pap/vote-places/near?latitude=48.879001640&longitude=2.3187434"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be a superset of:
            """
            [
                {
                    "uuid": "1cc8f1bf-533d-4c3a-a02b-00ba651e056a",
                    "latitude": 48.879414,
                    "longitude": 2.319874,
                    "addresses": 2,
                    "distance": 0.09452285753944736
                },
                {},
                {
                    "uuid": "8788d1df-9807-45db-a79a-3e1c03df141b",
                    "latitude": 48.877132,
                    "longitude": 2.341905,
                    "addresses": 2,
                    "distance": 1.7064951008410147
                },
                {},
                {
                    "uuid": "dcaec65c-0856-4c27-adf5-6d51593601e3",
                    "latitude": 48.862911,
                    "longitude": 2.341939,
                    "addresses": 2,
                    "distance": 2.465618433481793
                }
            ]
            """
