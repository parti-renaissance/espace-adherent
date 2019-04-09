Feature:
  As an adherent or CP administrator
  In order to see the CP information
  I should be able to acces CP page

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData        |
      | LoadTurnkeyProjectData  |
      | LoadCitizenProjectData  |

  @javascript
  Scenario: I can show and hide skills if there are more than 3
    Given I am logged as "jacques.picard@en-marche.fr"
    And I am on "/projets-citoyens/75008-le-projet-citoyen-a-paris-8"
    Then I should see "Paysage"
    And I should see "Jardinage / Botanique"
    And I should see "Gestion des parcs nationaux"
    And I should not see "Isolation thermique et acoustique"
    And I should not see "Horticulture"
    When I click the "show-more-skills" element
    Then I should see "Paysage"
    And I should see "Jardinage / Botanique"
    And I should see "Gestion des parcs nationaux"
    And I should see "Isolation thermique et acoustique"
    And I should see "Horticulture"
    When I click the "show-less-skills" element
    Then I should see "Paysage"
    And I should see "Jardinage / Botanique"
    And I should see "Gestion des parcs nationaux"
    And I should not see "Isolation thermique et acoustique"
    And I should not see "Horticulture"

    When I am on "/projets-citoyens/13003-le-projet-citoyen-a-dammarie-les-lys"
    Then I should see "Professeurs du primaire"
    And I should see "Professeurs du secondaire"
    And I should see "Professeurs d’université"
    And I should not see an "show-more-skills" element

    When I am on "/projets-citoyens/10019-projet-citoyen-a-new-york-city"
    Then I should see "Aucune compétence recherchée pour l'instant."
    And I should not see an "show-more-skills" element

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
