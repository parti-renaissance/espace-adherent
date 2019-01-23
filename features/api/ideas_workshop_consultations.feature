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
    When I send a "GET" request to "/api/ideas-workshop/consultations"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 2,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "response_time": 2,
                "started_at": "@string@.isDateTime()",
                "ended_at": "@string@.isDateTime()",
                "url": "https://fr.lipsum.com/",
                "name": "Consultation sur les retraites"
            }
        ]
    }
    """

