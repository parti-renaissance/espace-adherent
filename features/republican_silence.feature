@app
Feature:
  As Referent|Deputy|CP-Host|Committee-Host or Committee-Supervisor
  I cannot communicate with the adherents when one republican silence is declared for the same Referent Tags

  Scenario Outline: As committee host I cannot access to the committee pages
    Given I am logged as "lolodie.dutemps@hotnix.tld"
    When I go to "<uri>"
    Then I should see "En raison du silence républicain, votre espace est momentanément désactivé. Vous pourrez de nouveau y accéder à la fin de celui-ci."
    Examples:
      | uri                                                       |
      | /comites/en-marche-comite-de-singapour                    |
      | /comites/en-marche-comite-de-singapour/evenements/ajouter |

  Scenario: As committee host I cannot access to member contact page
    Given I am logged as "lolodie.dutemps@hotnix.tld"
      And I am on "/espace-animateur/en-marche-comite-de-singapour/messagerie"
     Then I should see "En raison du silence républicain, votre espace est momentanément désactivé. Vous pourrez de nouveau y accéder à la fin de celui-ci."

  Scenario: As deputy of 75001 I cannot communicate with adherents from my deputy space.
    Given I am logged as "deputy@en-marche-dev.fr"
    When I go to "/espace-depute/messagerie"
    Then I should see "En raison du silence républicain, votre espace est momentanément désactivé. Vous pourrez de nouveau y accéder à la fin de celui-ci."
