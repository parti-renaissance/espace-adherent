@api
Feature:
  In order to see polls and vote
  I should be able to access API of polls

  Background:
  Given the following fixtures are loaded:
    | LoadClientData |
    | LoadPollData   |

  Scenario: As a non logged-in user I can retrieve polls, vote for it and see the results
  Given I am logged with device "dd4SOCS-4UlCtO-gZiQGDA" via OAuth client "JeMarche App" with scope "jemarche_app"
  When I send a "GET" request to "/api/v3/polls"
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
        "question": "Plutôt thé ou café ?",
        "finish_at": "@string@.isDateTime()",
        "uuid": "8adca369-938c-450b-92e9-9c2b1f206fa3",
        "result": {
          "total": 4,
          "choices": [
            {
              "choice": {
                "value": "Thé",
                "uuid": "dd429c8f-a07f-47ad-a424-b28058c4bf7d"
              },
              "count": 0,
              "percentage": 0
            },
            {
              "choice": {
                "value": "Café",
                "uuid": "26aba15c-b49a-4cb7-99ef-585e12bcff50"
              },
              "count": 3,
              "percentage": 75
            },
            {
              "choice": {
                "value": "Ni l'un ni l'autre",
                "uuid": "c140e1fb-749c-4b13-97f6-327999004247"
              },
              "count": 1,
              "percentage": 25
            }
          ]
        }
      }
    ]
  }
  """

  When I send a "POST" request to "/api/v3/polls/vote" with body:
    """
    {
      "uuid": "26aba15c-b49a-4cb7-99ef-585e12bcff50"
    }
    """
  Then the response status code should be 201
  And the response should be in JSON
  And the JSON should be equal to:
  """
  {
    "question": "Plutôt thé ou café ?",
    "finish_at": "@string@.isDateTime()",
    "uuid": "8adca369-938c-450b-92e9-9c2b1f206fa3",
    "result": {
      "total": 5,
      "choices": [
        {
          "choice": {
            "value": "Thé",
            "uuid": "dd429c8f-a07f-47ad-a424-b28058c4bf7d"
          },
          "count": 0,
          "percentage": 0
        },
        {
          "choice": {
            "value": "Café",
            "uuid": "26aba15c-b49a-4cb7-99ef-585e12bcff50"
          },
          "count": 4,
          "percentage": 80
        },
        {
          "choice": {
            "value": "Ni l'un ni l'autre",
            "uuid": "c140e1fb-749c-4b13-97f6-327999004247"
          },
          "count": 1,
          "percentage": 20
        }
      ]
    }
  }
  """
