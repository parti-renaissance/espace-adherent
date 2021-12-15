@api
Feature:
  In order to create a JeMengage user
  As a non logged-in user
  I should be able to access API membership

  Scenario: As a non logged-in user I can create a JeMengage user
    Given I send a "POST" request to "/api/membership?source=jemengage" with body:
    """
    {
      "email_address": "new-user@en-marche-dev.fr",
      "first_name": "Jules",
      "last_name": "Fullstack",
      "gender": "male",
      "birthdate": "1975-01-01",
      "nationality": "FR",
      "phone": "0611223344",
      "address": {
          "address": "6 rue neyret",
          "postal_code": "69001",
          "city_name": "lyon 1er",
          "country": "FR"
      },
      "cgu_accepted": true
    }
    """
    Then the response status code should be 201
