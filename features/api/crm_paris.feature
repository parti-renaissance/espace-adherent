@api
Feature:
  In order to synchronize with CRM Paris application
  I should be able to download datas regarding adherents of Paris via the API

  Scenario:
    Given the following fixtures are loaded:
      | LoadAdherentData |
      | LoadClientData   |
    And I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | cChiFrOxtYb4CgnKoYvV9evEcrOsk2hb9wvO73QLYyc= |
      | client_id     | 40bdd6db-e422-4153-819c-9973c09f9297         |
      | grant_type    | client_credentials                           |
      | scope         | crm_paris                                    |
    Then I add the access token to the Authorization header
    And I send a "GET" request to "/api/crm-paris/adherents"
    Then the response status code should be 200
    And the header "Content-Type" should be equal to "text/csv; charset=UTF-8"
