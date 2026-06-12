@api
@renaissance_api
Feature:
    In order to keep /api/v3 reserved to authenticated users
    A device-only OAuth token must not be able to reach /api/v3 endpoints

    Scenario: A device token is rejected on /api/v3 by the ROLE_USER access control
        Given I am logged with device "device_2" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
        When I send a "PUT" request to "/api/v3/device/device_2" with body:
            """
            {
                "postal_code": "06200"
            }
            """
        Then the response status code should be 403
