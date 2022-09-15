@app
Feature: The goal is to donate one time or multiple time with a subscription
  In order to donate
  As an anonymous user or connected user
  I should be able to donate punctually or subscribe foreach month

  Scenario Outline: The user have to be able to go to donation page every where
    Given I am on "<url>"
    And the response status code should be 200
    Then I should see "Donner"

    When I follow "Donner"
    Then I should be on "/don"

    Examples:
      | url           |
      | /             |
      | /le-mouvement |
      | /evenements   |
      | /comites      |
      | /formation    |
      | /articles     |

  Scenario: A user can't donate more than 7500€ per year
    Given I freeze the clock to "2020-01-12"
    And I am logged as "jacques.picard@en-marche.fr"
    And I am on "/don/coordonnees?montant=7490&abonnement=0"
    And I press "Finaliser mon don"
    And I should see "Le don que vous vous apprêtez à faire est trop élevé. Les dons étant limités à 7500 euros par an et par personne, veuillez vous rapprocher du Pôle Finance pour connaitre votre limite annuelle."
