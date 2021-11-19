@app
Feature:
  As a visitor
  I should be able to check if I'm mandated and specify the mandate
  As an adherent
  I should be able to change my mandate on my profile

  Scenario: As a visitor, I should be able to register with my mandate informations
    And I am on "/adhesion"
    And I fill in the following:
      | adherent_registration[firstName]            | Jean-Pierre         |
      | adherent_registration[lastName]             | DURAND              |
      | adherent_registration[emailAddress][first]  | jp@test.com         |
      | adherent_registration[emailAddress][second] | jp@test.com         |
      | adherent_registration[password]             | testtest            |
      | adherent_registration[address][address]     | 1 rue des alouettes |
      | adherent_registration[address][postalCode]  | 94320               |
      | adherent_registration[address][cityName]    | Thiais              |
      | adherent_registration[birthdate][day]       | 29                  |
      | adherent_registration[birthdate][month]     | 1                   |
      | adherent_registration[birthdate][year]      | 1989                |
      | adherent_registration[gender]               | male                |
      | adherent_registration[nationality]          | FR                  |
    And I fill in hidden field "adherent_registration_address_city" with "94320-94073"
    And I fill in hidden field "adherent_registration_address_country" with "FR"
    And I fill in hidden field "adherent_registration_mandates" with "regional_councilor"
    And I check "Oui, j'adhère à la charte des valeurs, aux statuts et aux règles de fonctionnement de La République En Marche, ainsi qu'aux conditions générales d'utilisation du site"
    And I resolved the captcha
    And I clean the "api_sync" queue
    When I press "Je rejoins La République En Marche"
    Then the response status code should be 200
    And I should be on "/inscription/centre-interets"

