@api
Feature:
  In order to see an idea
  As a non logged-in user
  I should be able to access API Ideas Workshop

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaData         |
      | LoadIdeaCommentData  |

  Scenario: As a non logged-in user I can see published ideas
    When I send a "GET" request to "/api/ideas-workshop/ideas"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
       {
          "days_before_deadline":21,
          "uuid":"e4ac3efc-b539-40ac-9417-b60df432bdc5",
          "answers":[
             {
                "threads":[
                   {
                      "comments":[
                         {
                            "author":{
                               "firstName":"Benjamin",
                               "lastName":"Duroc"
                            }
                         },
                         {
                            "author":{
                               "firstName":"Francis",
                               "lastName":"Brioul"
                            }
                         },
                         {
                            "author":{
                               "firstName":"Referent",
                               "lastName":"Referent"
                            }
                         }
                      ]
                   }
                ]
             },
             {
                "threads":[
                   {

                   }
                ]
             },
             {
                "threads":[
                   {
                      "comments":[
                         {
                            "author":{
                               "firstName":"Laura",
                               "lastName":"Deloche"
                            }
                         }
                      ]
                   }
                ]
             }
          ],
          "author":{
             "firstName":"Jacques",
             "lastName":"Picard"
          },
          "published_at":"2018-12-01T10:00:00+01:00",
          "committee":{
             "name":"En Marche Paris 8",
             "slug":"en-marche-paris-8"
          },
          "name":"Faire la paix",
          "slug":"faire-la-paix"
       }
    ]
    """

  Scenario: As a non logged-in user I can see pending ideas
    When I send a "GET" request to "/api/ideas-workshop/ideas?status=draft"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
       {
          "days_before_deadline":21,
          "uuid":"3b1ea810-115f-4b2c-944d-34a55d7b7e4d",
          "answers":[

          ],
          "author":{
             "firstName":"Jacques",
             "lastName":"Picard"
          },
          "published_at":"2018-12-02T10:00:00+01:00",
          "committee":{
             "name":"En Marche Paris 8",
             "slug":"en-marche-paris-8"
          },
          "name":"Favoriser l'\u00e9cologie",
          "slug":"favoriser-lecologie"
       },
       {
          "days_before_deadline":21,
          "uuid":"aa093ce6-8b20-4d86-bfbc-91a73fe47285",
          "answers":[

          ],
          "author":{
             "firstName":"Jacques",
             "lastName":"Picard"
          },
          "published_at":"2018-12-03T10:00:00+01:00",
          "name":"Aider les gens",
          "slug":"aider-les-gens"
       }
    ]
    """

  Scenario: As a non logged-in user I can filter notes by name
    When I send a "GET" request to "/api/ideas-workshop/ideas?name=paix"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
       {
          "days_before_deadline":21,
          "uuid":"e4ac3efc-b539-40ac-9417-b60df432bdc5",
          "answers":[
             {
                "threads":[
                   {
                      "comments":[
                         {
                            "author":{
                               "firstName":"Benjamin",
                               "lastName":"Duroc"
                            }
                         },
                         {
                            "author":{
                               "firstName":"Francis",
                               "lastName":"Brioul"
                            }
                         },
                         {
                            "author":{
                               "firstName":"Referent",
                               "lastName":"Referent"
                            }
                         }
                      ]
                   }
                ]
             },
             {
                "threads":[
                   {

                   }
                ]
             },
             {
                "threads":[
                   {
                      "comments":[
                         {
                            "author":{
                               "firstName":"Laura",
                               "lastName":"Deloche"
                            }
                         }
                      ]
                   }
                ]
             }
          ],
          "author":{
             "firstName":"Jacques",
             "lastName":"Picard"
          },
          "published_at":"2018-12-01T10:00:00+01:00",
          "committee":{
             "name":"En Marche Paris 8",
             "slug":"en-marche-paris-8"
          },
          "name":"Faire la paix",
          "slug":"faire-la-paix"
       }
    ]
    """
