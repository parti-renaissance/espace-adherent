Feature:
  As an adherent or CP administrator
  In order to see the CP information
  I should be able to acces CP page

  Scenario: As an administrator I should see the documents of an approved CP created from a turnkey project
    Given I am logged as "lolodie.dutemps@hotnix.tld"
    And I am on "/projets-citoyens/13003-un-stage-pour-tous"
    Then I should see "Mon kit"
    And I follow "Documentation pour le créateur du projet"
    Then I should be on "/projets-citoyens/kits/document-referent-a.pdf"
    And the response status code should be 200

  Scenario: As a follower I should not see the documents of an approved CP created from a turnkey project
    Given I am logged as "jacques.picard@en-marche.fr"
    And I am on "/projets-citoyens/13003-un-stage-pour-tous"
    Then I should not see "Mon kit"
    And I should not see "Documentation pour le créateur du projet"

  Scenario: As an anonymous I should not see the documents of an approved CP created from a turnkey project
    Given I am on "/projets-citoyens/13003-un-stage-pour-tous"
    Then I should not see "Mon kit"
    And I should not see "Documentation pour le créateur du projet"
