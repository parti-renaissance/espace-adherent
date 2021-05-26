@committeeVote
Feature:
  As an adherent I should be able to vote/unvote in followed committees

  Scenario: As member of the committee, I cannot leave it during an election
    Given I am logged as "assesseur@en-marche-dev.fr"

    When I am on "/comites/en-marche-comite-de-berlin"
    Then I should see "Désignation du binôme d’adhérents siégeant au Conseil territorial"
    And I should not see "Quitter ce comité"

    When I am on "/comites/en-marche-comite-de-evry"
    Then I should not see "Désignation du binôme d’adhérents siégeant au Conseil territorial"
    And I should not see "Élection du binôme paritaire d’Animateurs locaux"
    And I should see "Quitter ce comité"
