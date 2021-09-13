@skip
Feature:
  As an APP
  In order to sync all users with API
  RabbitMQ messages should be published on user events

  Scenario Outline: Publish message on user created|updated
    Given the following fixtures are loaded:
      | LoadAdherentData |
    Given I clean the "api_sync" queue
    When I dispatch the "<event>" user event with "michelle.dufour@example.ch"
    Then "api_sync" should have 1 message
    And "api_sync" should have message below:
      | routing_key   | body                                                                                                                                                                                                                                |
      | <routing_key> | {"uuid":"313bd28f-efc8-57c9-8ab7-2106c8be9697","subscriptionExternalIds":["123abc","456def"],"city":"Zürich","country":"CH","zipCode":"8057","tags":["CH"],"emailAddress":"michelle.dufour@example.ch","firstName":"Michelle","lastName":"Dufour"}  |
    Examples:
      | event        | routing_key  |
      | user.created | user.created |
      | user.updated | user.updated |

  Scenario: Publish message on user deleted
    Given the following fixtures are loaded:
      | LoadAdherentData |
    Given I clean the "api_sync" queue
    When I dispatch the "user.deleted" user event with "michel.vasseur@example.ch"
    Then "api_sync" should have 1 message
    And "api_sync" should have message below:
      | routing_key  | body                                                                                                                                                                                                                             |
      | user.deleted | {"uuid":"46ab0600-b5a0-59fc-83a7-cc23ca459ca0","subscriptionExternalIds":["123abc","456def"],"city":"Kilchberg","country":"CH","zipCode":"8802","tags":["CH"],"emailAddress":"michel.vasseur@example.ch","firstName":"Michel","lastName":"VASSEUR"} |

  Scenario: Publish message on user update subscriptions
    Given the following fixtures are loaded:
      | LoadAdherentData |
    Given I clean the "api_sync" queue
    When I dispatch the "user.update_subscriptions" user event with "jacques.picard@en-marche.fr" and email subscriptions
    Then "api_sync" should have 1 message
    And "api_sync" should have message below:
      | routing_key               | body                                                                                            |
      | user.update_subscriptions | {"uuid":"a046adbe-9c7b-56a9-a676-6151a6785dda","subscriptions":["123abc"],"unsubscriptions":[]} |

  Scenario Outline: Publish message on committee created|updated
    Given the following fixtures are loaded:
      | LoadCommitteeData |
    And I clean the "api_sync" queue
    When I dispatch the "<event>" committee event with "En Marche Paris 8"
    Then "api_sync" should have 1 message
    And "api_sync" should have message below:
      | routing_key   | body                                                                                                                                                                                                                                                                                                    |
      | <routing_key> | {"uuid":"515a56c0-bde8-56ef-b90c-4745b1c93818","name":"En Marche Paris 8","slug":"en-marche-paris-8","status":"APPROVED","membersCount":4,"tags":["75008","75"],"longitude":2.313243,"latitude":48.870506,"country":"FR","address":"60 avenue des Champs-Élysées","zipCode":"75008","city":"Paris 8e"}  |
    Examples:
      | event             | routing_key       |
      | committee.created | committee.created |
      | committee.updated | committee.updated |

  Scenario: Publish message on committee deleted
    Given the following fixtures are loaded:
      | LoadCommitteeData |
    And I clean the "api_sync" queue
    When I dispatch the "committee.deleted" committee event with "En Marche Paris 8"
    Then "api_sync" should have 1 message
    And "api_sync" should have message below:
      | routing_key       | body                                            |
      | committee.deleted | {"uuid":"515a56c0-bde8-56ef-b90c-4745b1c93818"} |

  Scenario Outline: Publish message on event created|updated
    Given the following fixtures are loaded:
      | LoadAdherentData      |
      | LoadEventCategoryData |
      | LoadCommitteeEventData |
    And I clean the "api_sync" queue
    When I dispatch the "<event>" event event with "Réunion de réflexion parisienne"
    Then "api_sync" should have "<nb_message>" message
    And "api_sync" should have message below:
      | routing_key   | body                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
      | <routing_key> | {"uuid":"1fc69fd0-2b34-4bd4-a0cc-834480480934","country":"FR","address":"60 avenue des Champs-Élysées","zipCode":"75008","city":"Paris 8e","latitude":48.870506,"longitude":2.313243,"name":"Réunion de réflexion parisienne","slug":"@string@.endsWith('-reunion-de-reflexion-parisienne')","beginAt":"@string@.isDateTime()","finishAt":"@string@.isDateTime()","participantsCount":1,"status":"SCHEDULED","capacity":50,"committeeUuid":"515a56c0-bde8-56ef-b90c-4745b1c93818","categoryName":"Atelier du programme","tags":["75008","75"],"organizerUuid":"a046adbe-9c7b-56a9-a676-6151a6785dda", "timeZone": "Europe/Paris"} |
    Examples:
      | event         | routing_key   | nb_message |
      | event.created | event.created | 2          |
      | event.updated | event.updated | 1          |

  Scenario: Publish message on event deleted
    Given the following fixtures are loaded:
      | LoadAdherentData      |
      | LoadEventCategoryData |
      | LoadCommitteeEventData         |
    And I clean the "api_sync" queue
    When I dispatch the "event.deleted" event event with "Réunion de réflexion parisienne"
    Then "api_sync" should have 1 message
    And "api_sync" should have message below:
      | routing_key   | body                                            |
      | event.deleted | {"uuid":"1fc69fd0-2b34-4bd4-a0cc-834480480934"} |
