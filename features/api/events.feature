Feature:
  In order to get events count in the
  As a referent
  I should be able to access events API stats

  Background:
    Given I freeze the clock to "2018-05-18"
    And the following fixtures are loaded:
      | LoadUserData          |
      | LoadAdherentData      |
      | LoadEventData         |
      | LoadCitizenActionData |

  Scenario: As a non logged-in user I can not get events count in the referent managed zone
    When I am on "/api/events/count-by-month?country=fr"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not get events count in the referent managed zone
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/events/count-by-month?country=fr"
    Then the response status code should be 403

  Scenario: As a referent I can get events count in the referent managed zone
    When I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "/api/events/count-by-month"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-05":{"events":2},
      "2018-04":{"events":0},
      "2018-03":{"events":0},
      "2018-02":{"events":0},
      "2018-01":{"events":0},
      "2017-12":{"events":0}
    }
    """

    When I am on "/api/events/count-by-month?country=fr"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-05":{"events":2},
      "2018-04":{"events":0},
      "2018-03":{"events":0},
      "2018-02":{"events":0},
      "2018-01":{"events":0},
      "2017-12":{"events":0}
    }
    """

    When I am on "/api/events/count-by-month?city=Paris 8e"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-05":{"events":2},
      "2018-04":{"events":0},
      "2018-03":{"events":0},
      "2018-02":{"events":0},
      "2018-01":{"events":0},
      "2017-12":{"events":0}
    }
    """

    When I am on "/api/events/count-by-month?city=Fontainebleau"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "2018-05":{"events":0},
        "2018-04":{"events":0},
        "2018-03":{"events":0},
        "2018-02":{"events":0},
        "2018-01":{"events":0},
        "2017-12":{"events":0}
      }
    """

    When I am on "/api/events/count-by-month?committee=515a56c0-bde8-56ef-b90c-4745b1c93818"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-05":{"events":2},
      "2018-04":{"events":0},
      "2018-03":{"events":0},
      "2018-02":{"events":0},
      "2018-01":{"events":0},
      "2017-12":{"events":0}
    }
    """

    # Test get stats for committee with scheduled events but not managed by referent
    When I am on "/api/events/count-by-month?committee=62ea97e7-6662-427b-b90a-23429136d0dd"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "2018-05":{"events":0},
      "2018-04":{"events":0},
      "2018-03":{"events":0},
      "2018-02":{"events":0},
      "2018-01":{"events":0},
      "2017-12":{"events":0}
    }
    """
