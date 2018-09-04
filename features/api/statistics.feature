Feature:
  In order to get the acquisition statistics data
  I should be able to request them via the API

  Scenario:
    Given the following fixtures are loaded:
      | LoadClientData |
    And I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                        |
      | client_secret | crOsk2OxtYb4CgnKoYvhb9wvO73QLYyccChiFrV9evE= |
      | client_id     | 4f3394d4-7137-424a-8c73-27e0ad641fc9         |
      | grant_type    | client_credentials                           |
      | scope         | read:stats                                   |
    And I add the access token to the Authorization header
    When I send a "GET" request to "/api/statistics/acquisition?start-date=01-01-2018&end-date=31-03-2018&tags%5B%5D=CH"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "root" should have 21 elements
    And the JSON nodes should contain:
      | root[0].Adherent (total).201801                                         | |
      | root[0].Adherent (total).201802                                         | |
      | root[0].Adherent (total).201803                                         | |
      | root[1].Montant dons mensuels (total).201801                            | |
      | root[1].Montant dons mensuels (total).201802                            | |
      | root[1].Montant dons mensuels (total).201803                            | |
      | root[2].Montant dons ponctuels (total).201801                           | |
      | root[2].Montant dons ponctuels (total).201802                           | |
      | root[2].Montant dons ponctuels (total).201803                           | |
      | root[3].Comités (new).201801                                            | |
      | root[3].Comités (new).201802                                            | |
      | root[3].Comités (new).201803                                            | |
      | root[4].Adhérents membres de comités (total).201801                     | |
      | root[4].Adhérents membres de comités (total).201802                     | |
      | root[4].Adhérents membres de comités (total).201803                     | |
      | root[5].Adherents inscrits à des événements (total).201801              | |
      | root[5].Adherents inscrits à des événements (total).201802              | |
      | root[5].Adherents inscrits à des événements (total).201803              | |
      | root[6].Événements (new).201801                                         | |
      | root[6].Événements (new).201802                                         | |
      | root[6].Événements (new).201803                                         | |
      | root[7].Inscrits à des événements (total).201801                        | |
      | root[7].Inscrits à des événements (total).201802                        | |
      | root[7].Inscrits à des événements (total).201803                        | |
      | root[8].Non-adherents inscrits à des événements (total).201801          | |
      | root[8].Non-adherents inscrits à des événements (total).201802          | |
      | root[8].Non-adherents inscrits à des événements (total).201803          | |
      | root[9].Dons mensuels par des adhérents (total).201801                  | |
      | root[9].Dons mensuels par des adhérents (total).201802                  | |
      | root[9].Dons mensuels par des adhérents (total).201803                  | |
      | root[10].Dons mensuels (total).201801                                   | |
      | root[10].Dons mensuels (total).201802                                   | |
      | root[10].Dons mensuels (total).201803                                   | |
      | root[11].Adherents (new).201801                                         | |
      | root[11].Adherents (new).201802                                         | |
      | root[11].Adherents (new).201803                                         | |
      | root[12].Adhérents inscrits à la lettre du vendredi (total).201801      | |
      | root[12].Adhérents inscrits à la lettre du vendredi (total).201802      | |
      | root[12].Adhérents inscrits à la lettre du vendredi (total).201803      | |
      | root[13].Adhérents inscrits aux mails de leur référent (total).201801   | |
      | root[13].Adhérents inscrits aux mails de leur référent (total).201802   | |
      | root[13].Adhérents inscrits aux mails de leur référent (total).201803   | |
      | root[14].Inscrits à la lettre du vendredi (total).201801                | |
      | root[14].Inscrits à la lettre du vendredi (total).201802                | |
      | root[14].Inscrits à la lettre du vendredi (total).201803                | |
      | root[15].Inscrits à la liste globale (total).201801                     | |
      | root[15].Inscrits à la liste globale (total).201802                     | |
      | root[15].Inscrits à la liste globale (total).201803                     | |
      | root[16].Comités en attente (new).201801                                | |
      | root[16].Comités en attente (new).201802                                | |
      | root[16].Comités en attente (new).201803                                | |
      | root[17].Dons ponctuels par des adherents (total).201801                | |
      | root[17].Dons ponctuels par des adherents (total).201802                | |
      | root[17].Dons ponctuels par des adherents (total).201803                | |
      | root[18].Dons ponctuels (total).201801                                  | |
      | root[18].Dons ponctuels (total).201802                                  | |
      | root[18].Dons ponctuels (total).201803                                  | |
      | root[19].Ratio membre de comite par nbr adherents (total).201801        | |
      | root[19].Ratio membre de comite par nbr adherents (total).201802        | |
      | root[19].Ratio membre de comite par nbr adherents (total).201803        | |
      | root[20].Desadhésions (new).201801                                      | |
      | root[20].Desadhésions (new).201802                                      | |
      | root[20].Desadhésions (new).201803                                      | |
