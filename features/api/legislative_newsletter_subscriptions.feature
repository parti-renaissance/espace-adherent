@api
Feature:
  In order to subscribe to legislative candidate newsletter
  As software developer
  I should be able to access legislative newsletter subscription API

  Scenario: As a non logged-in I can subscribe to a candidate newsletter
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/legislative_newsletter_subscriptions" with body:
    """
    {
      "email_address": "lucile@example.org",
      "first_name": "lucile",
      "postal_code": "75001",
      "country": "FR",
      "from_zone": "75-1",
      "personal_data_collection": true
    }
    """
    Then the response status code should be 201

  Scenario: As a non logged-in user I cannot subscribe twice time to a candidate newsletter in the same district
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/legislative_newsletter_subscriptions" with body:
    """
    {
      "email_address": "john@example.org",
      "first_name": "Jone",
      "postal_code": "75008",
      "country": "FR",
      "from_zone": "75-8",
      "personal_data_collection": true
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "email_address: Vous êtes déjà inscrit à la newsletter de cette circonscription.",
      "violations": [
        {
          "propertyPath": "email_address",
          "message": "Vous êtes déjà inscrit à la newsletter de cette circonscription."
        }
      ]
    }
    """
