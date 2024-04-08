@app
Feature:
  As deputy
  I can send messages to the adherents, see committees and events of my district

  Scenario Outline: As anonymous I can not access deputy space pages.
    Given I go to "<uri>"
    Then the response status code should be 200
    And I should be on "/connexion"
    Examples:
      | uri                                   |
      | /espace-depute/messagerie             |
      | /espace-depute/evenements             |
      | /espace-depute/comites                |

  Scenario Outline: As simple adherent I can not access deputy space pages.
    Given I am logged as "carl999@example.fr"
    When I go to "<uri>"
    Then the response status code should be 403
    Examples:
      | uri                                   |
      | /espace-depute/messagerie             |
      | /espace-depute/evenements             |
      | /espace-depute/comites                |

  Scenario Outline: As deputy of 1st Paris district I can access deputy space pages.
    Given I am logged as "deputy@en-marche-dev.fr"
    When I go to "<uri>"
    Then the response status code should be 200
    Examples:
      | uri                                   |
      | /espace-depute/messagerie             |
      | /espace-depute/evenements             |
      | /espace-depute/comites                |

  Scenario: As deputy of 1st Paris district I can see events.
    Given I am logged as "deputy@en-marche-dev.fr"
    When I am on "/espace-depute/evenements"
    Then I should see 15 "table.datagrid__table-manager tbody tr" elements
    And I should see "Événement de la catégorie masquée"
    And I should see "Réunion de réflexion parisienne annulé"
    And I should see "Réunion de réflexion parisienne"
    And I should see "Un événement du candidat aux législatives"
    And I should see "Référent event"
    And I should see "Grand débat parisien"
    And I should see "Événement à Paris 1"
    And I should see "Événement à Paris 2"
    And I should see "Marche Parisienne"
    And I should see "Grand Meeting de Paris"
