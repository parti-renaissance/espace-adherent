Feature:
  As a referent or an animator
  I should be able to access my messages from my dashboard

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |
      | LoadEventData    |

  Scenario: As an animator, I have a button on the dashboard to go to my messages
    Given I am logged as "jacques.picard@en-marche.fr"
    And I am on "espace-adherent/tableau-de-bord"
    And I should see "Les e-mails que j'ai envoyés"
    When I follow "Voir tous les emails"
    Then the response status code should be 200
    And I should be on "espace-adherent/tableau-de-bord/mes-messages"

  Scenario: As an animator, I have a button on my messages page to go back to the dashboard
    Given I am logged as "jacques.picard@en-marche.fr"
    And I am on "espace-adherent/tableau-de-bord/mes-messages"
    When I follow "Retour au dashboard"
    Then the response status code should be 200
    And I should be on "espace-adherent/tableau-de-bord"

  @javascript
  Scenario: As an animator, I should see my messages
    Given I am logged as "jacques.picard@en-marche.fr"
    And I am on "espace-adherent/tableau-de-bord/mes-messages"
    And I should see "Retour au dashboard"
    And I should see "38 résultat(s)"
    And I should see "Sujet"
    And I should see "Contenu"
    And I should see "Nombre de destinataires"
    And I should see "[Comité local] Foo subject"
    And I should see "Rapport d'activité du 7 janvier 2017."
