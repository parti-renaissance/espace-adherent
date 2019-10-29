@api
Feature:
  In order to see consultation reports
  As a non logged-in user
  I should be able to access Ideas Workshop API

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaConsultationReportData |

  Scenario: As a non logged-in user I can see consultation reports
    When I send a "GET" request to "/api/ideas-workshop/consultation_reports"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 2,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "url": "https://storage.googleapis.com/en-marche-prod/documents/adherents/1-charte-et-manifeste/charte_des_valeurs.pdf",
                "position": 1,
                "name": "Rapport sur les énergies renouvables"
            },
            {
                "url": "https://storage.googleapis.com/en-marche-prod/documents/adherents/1-charte-et-manifeste/LaREM_regles_de_fonctionnement.pdf",
                "position": 2,
                "name": "Rapport sur la politique du logement"
            }
        ]
    }
    """
