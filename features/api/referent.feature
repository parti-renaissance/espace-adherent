Feature:
  In order to get information for referent space
  As a referent
  I should be able to acces API data accessible by referent

  Background:
    Given the following fixtures are loaded:
      | LoadUserData      |
      | LoadAdherentData  |
      | LoadEventData     |

  Scenario: As a non logged-in user I can not get the committee, cities and countries managed by referent for autocomplete
    When I am on "/api/referent/search/autocomplete?type=committee&value=en"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not get the committee, cities and countries managed by referent for autocomplete
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/referent/search/autocomplete?type=committee&value=en"
    Then the response status code should be 403

  Scenario: As a referent I can get the committee, cities and countries managed by referent for autocomplete
    When I am logged as "referent@en-marche-dev.fr"
    And I am on "/api/referent/search/autocomplete?type=committee&value=en"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "committees":[
        {"508d4ac0-27d6-4635-8953-4cc8600018f9":"En Marche - Comit\u00e9 de Rouen"},
        {"79638242-5101-11e7-b114-b2f933d5fe66":"En Marche - Suisse"},
        {"b0cd0e52-a5a4-410b-bba3-37afdd326a0a":"En Marche Dammarie-les-Lys"}
      ]
    }
    """

    When I am on "/api/referent/search/autocomplete?type=country&value=s"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "countries":[{"CH":"Suisse"}]
    }
    """

    # Test that search is case insensitive
    When I am on "/api/referent/search/autocomplete?type=city&value=FON"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "cities":["Fontainebleau"]
    }
    """
