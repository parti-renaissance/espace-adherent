@api @rol
Feature:
  In order to manipulate ideas
  As an adherent
  I need to be able to get/ctreate/update ideas.

  Background:
    Given I freeze the clock to "2018-12-01"
    And the following fixtures are loaded:
      | LoadAdherentData      |
      | LoadIdeaData          |
      | LoadIdeaNeedData      |
      | LoadIdeaCategoryData  |
      | LoadIdeaThemeData     |
      | LoadIdeaThreadData    |
      | LoadIdeaCommentData   |
      | LoadIdeaGuidelineData |
      | LoadIdeaQuestionData  |
      | LoadIdeaAnswerData    |

  Scenario: As a non logged-in user I can not create an idea
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/ideas" with body:
    """
    {
      "name": "My first note",
      "theme": "1",
      "category": "1",
      "needs": [
        "1",
        "2"
      ],
      "adherent": "3",
      "committee": "1",
      "answers": [
        {
          "content": "Je constate le problème.",
          "question": "1"
        },
        {
          "content": "Je résous le problème.",
          "question": "2"
        }
      ]
    }
    """
    Then the response status code should be 401

  Scenario: As a logged user I can create an idea
    When I am logged as "jacques.picard@en-marche.fr"
    And I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/ideas" with body:
    """
    {
      "name": "My first note",
      "theme": "1",
      "category": "1",
      "needs": [
        "1",
        "2"
      ],
      "adherent": "3",
      "committee": "1",
      "answers": [
        {
          "content": "Je constate le problème.",
          "question": "1"
        },
        {
          "content": "Je résous le problème.",
          "question": "2"
        },
      ]
    }
    """
    Then the response status code should be 201

  Scenario: As a non logged-in user I can not update an idea
    When I add "Accept" header equal to "application/hal+json"
    And I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/ideas/1" with body:
    """
    {
      "name": "My first note",
      "theme": "1",
      "category": "1",
      "needs": [
        "1",
        "2"
      ],
      "adherent": "3",
      "committee": "1",
      "answers": [
        {
          "content": "Je constate le problème.",
          "question": "1"
        },
        {
          "content": "Je résous le problème.",
          "question": "2"
        },
      ]
    }
    """
    Then the response status code should be 401

  Scenario: As an adherent can not update an idea another than owned
    When I am logged as "michel.vasseur@example.ch"
    And I add "Accept" header equal to "application/hal+json"
    And I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/ideas/1" with body:
    """
    {
      "name": "My first note",
      "theme": "1",
      "category": "1",
      "needs": [
        "1",
        "2"
      ],
      "adherent": "3",
      "committee": "1",
      "answers": [
        {
          "content": "Je constate le problème.",
          "question": "1"
        },
        {
          "content": "Je résous le problème.",
          "question": "2"
        },
      ]
    }
    """
    Then the response status code should be 406

  Scenario: As an idea's creator I can update it
    When I am logged as "jacques.picard@en-marche.fr"
    And I add "Accept" header equal to "application/hal+json"
    And I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/ideas/1" with body:
    """
    {
      "name": "My first note",
      "theme": "1",
      "category": "1",
      "needs": [
        "1",
        "2"
      ],
      "adherent": "3",
      "committee": "1",
      "answers": [
        {
          "content": "Je constate le problème.",
          "question": "1"
        },
        {
          "content": "Je résous le problème.",
          "question": "2"
        },
      ]
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the header "Content-Type" should be equal to "application/hal+json; charset=utf-8"
    And the JSON should be equal to:
    """
    {
      "name": "My first note",
      "theme": "1",
      "category": "1",
      "needs": [
        "1",
        "2"
      ],
      "adherent": "3",
      "committee": "1",
      "answers": [
        {
          "content": "Je constate le problème.",
          "question": "1"
        },
        {
          "content": "Je résous le problème.",
          "question": "2"
        },
      ]
    }
    """
