@api
@renaissance
Feature:
  Scenario: RE site can send a webhook for save adhesion email address
    When I send a "POST" request to "/adhesion/webhook/testKey123" with body:
    """
    {"email": "test@email.code"}
    """
    Then the response status code should be 200
    When I send a "POST" request to "/adhesion/webhook/testKey123" with body:
    """
    {"email": "test@email.code"}
    """
    Then the response status code should be 200
