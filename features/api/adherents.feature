Feature:
  In order to get adherents' information
  As a referent
  I should be able to acces adherents API data

  Background:
    Given I freeze the clock to "2018-04-17"
    And the following fixtures are loaded:
      | LoadUserData                       |
      | LoadAdherentData                   |
      | LoadEmailSubscriptionHistoryData   |
      | LoadCommitteeMembershipHistoryData |

  Scenario: As a non logged-in user I can not access the adherents count information
    When I am on "/api/adherents/count"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not access the adherents count information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/adherents/count"
    Then the response status code should be 403

  Scenario: As a referent I can access the adherents count information
    Given I am logged as "referent@en-marche-dev.fr"
    When I am on "/api/adherents/count"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "female":7,"male":11,"total":18
    }
    """

  Scenario: As a non logged-in user I can not access the managed by referent adherents count information
    When I am on "/api/adherents/count-by-referent-area"
    Then the response status code should be 200
    And I should be on "/connexion"

  Scenario: As an adherent I can not access the managed by referent adherents count information
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/api/adherents/count-by-referent-area"
    Then the response status code should be 403

  Scenario: As a referent I can access the managed by referent adherents count information
    When I am logged as "referent-75-77@en-marche-dev.fr"
    And I am on "/api/adherents/count-by-referent-area"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "female":3,
      "male":4,
      "total":7,
      "monthly": {
          "2018-04": {"total": 6, "in_at_least_one_committee": 3},
          "2018-03": {"total": 6, "in_at_least_one_committee": 3},
          "2018-02": {"total": 6, "in_at_least_one_committee": 2},
          "2018-01": {"total": 6, "in_at_least_one_committee": 2},
          "2017-12": {"total": 5, "in_at_least_one_committee": 2},
          "2017-11": {"total": 5, "in_at_least_one_committee": 2}
      },
      "email_subscriptions": {
          "2018-04": {"subscribed_emails_local_host": 7, "subscribed_emails_referents": 7},
          "2018-03": {"subscribed_emails_local_host": 0, "subscribed_emails_referents": 0},
          "2018-02": {"subscribed_emails_local_host": 4, "subscribed_emails_referents": 0},
          "2018-01": {"subscribed_emails_local_host": 3, "subscribed_emails_referents": 0},
          "2017-12": {"subscribed_emails_local_host": 2, "subscribed_emails_referents": 0},
          "2017-11": {"subscribed_emails_local_host": 1, "subscribed_emails_referents": 0}
      }
    }
    """
