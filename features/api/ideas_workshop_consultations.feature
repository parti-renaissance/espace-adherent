@api
Feature:
  In order to see consultations
  As a non logged-in user
  I should be able to access API Ideas Workshop

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData          |
      | LoadIdeaConsultationData  |

  Scenario: As a non logged-in user I can see consultations
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/consultations"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        {
            "response_time": 2,
            "started_at": "@string@.isDateTime()",
            "ended_at": "@string@.isDateTime()",
            "url": "https://fr.lipsum.com/",
            "name": "Consultation sur les retraites"
        }
    ]
    """

