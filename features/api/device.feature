@api
Feature:
  In order to track device informations
  As a logged-in device
  I should be able to update my informations

  Scenario: As a logged-in device I can update my postal code
    Given I am logged with device "device_2" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "PUT" request to "/api/v3/device/device_2" with body:
    """
    {
      "postal_code": "06200"
    }
    """
    Then the response status code should be 200

  Scenario: As a logged-in device I can not update another device
    Given I am logged with device "device_2" via OAuth client "JeMengage Mobile" with scope "jemarche_app"
    And I add "Content-Type" header equal to "application/json"
    And I add "Accept" header equal to "application/json"
    When I send a "PUT" request to "/api/v3/device/device_1" with body:
    """
    {
      "postal_code": "06200"
    }
    """
    Then the response status code should be 403
