@api
Feature:
  In order to change email subscription modification via Webhook
  As a platform
  I need to be able to save changes of adherent email subscriptions.

  Scenario: The platform save email subscription modification from Webhook
    Given the following fixtures are loaded:
      | LoadAdherentData  |
    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/parametres/mon-compte/preferences-des-emails"
    Then the "Recevoir les e-mails nationaux" checkbox should be checked

    When I send a "POST" request to "/api/email-subscriptions/change?secret=mailchimp_secret" with parameters:
      | key           | value                       |
      | type          | unsubscribe                 |
      | data[email]   | jacques.picard@en-marche.fr |
      | data[list_id] | 123abc                      |
    Then the response status code should be 200
    And the response should contain "OK"

    When I send a "POST" request to "/api/email-subscriptions/change?secret=mailchimp_secret" with parameters:
      | key           | value                       |
      | type          | unsubscribe                 |
      | data[email]   | foobar@en-marche-dev.fr     |
      | data[list_id] | 123abc                      |
    Then the response status code should be 200
    And the response should contain "OK"

    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/parametres/mon-compte/preferences-des-emails"
    Then the "Recevoir les e-mails nationaux" checkbox should be unchecked

    When I send a "POST" request to "/api/email-subscriptions/change?secret=mailchimp_secret" with parameters:
      | key           | value                       |
      | type          | subscribe                   |
      | data[email]   | jacques.picard@en-marche.fr |
      | data[list_id] | 123abc                      |
    Then the response status code should be 200
    And the response should contain "OK"

    When I am logged as "jacques.picard@en-marche.fr"
    And I am on "/parametres/mon-compte/preferences-des-emails"
    Then the "Recevoir les e-mails nationaux" checkbox should be checked

  Scenario: The platform doesn't save email subscription modification if no secret
    When I send a "POST" request to "/api/email-subscriptions/change" with parameters:
      | key           | value                       |
      | type          | subscribe                   |
      | data[email]   | jacques.picard@en-marche.fr |
      | data[list_id] | 123abc                      |
    Then the response status code should be 401

  Scenario: The platform doesn't save email subscription modification if no secret
    When I send a "POST" request to "/api/email-subscriptions/change?secret=wrong" with parameters:
      | key           | value                       |
      | type          | subscribe                   |
      | data[email]   | jacques.picard@en-marche.fr |
      | data[list_id] | 123abc                      |
    Then the response status code should be 403

  Scenario: The platform doesn't save email subscription modification if no POST data
    When I send a "POST" request to "/api/email-subscriptions/change?secret=mailchimp_secret"
    Then the response status code should be 400

  Scenario: The platform doesn't save email subscription modification if POST data is not correct
    When I send a "POST" request to "/api/email-subscriptions/change?secret=mailchimp_secret" with parameters:
      | key           | value                       |
      | action        | subscribe                   |
      | data[address] | jacques.picard@en-marche.fr |
      | data[list]    | 123abc                      |
    Then the response status code should be 400
