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

  Scenario: As deputy of 1st Paris district I can send message to the adherents.
    Given I am logged as "deputy@en-marche-dev.fr"
    When I am on "/espace-depute/utilisateurs/message"
    Then the "recipient" field should contain "6 marcheur(s)"
    And the "sender" field should contain "Député PARIS I"

    # Try to send an empty form
    When I press "Envoyer le message"
    Then I should be on "/espace-depute/utilisateurs/message"
    And I should see 2 ".form__errors" elements
    And I should see "Cette valeur ne doit pas être vide."
    And I should see "Le contenu du message ne doit pas être vide."

    When I fill in the following:
      | deputy_message[subject] | Message from your deputy    |
      | deputy_message[content] | Content of a deputy message |
    And I press "Envoyer le message"
    Then I should be on "/espace-depute/utilisateurs/message"
    And I should see 0 ".form__errors" elements
    And I should see "Votre message a été envoyé avec succès. Il pourrait prendre quelques minutes à s'envoyer."

  Scenario: As deputy of 1st Paris district I can see events.
    Given I am logged as "deputy@en-marche-dev.fr"
    When I am on "/espace-depute/evenements"
    Then I should see 10 "table.datagrid__table-manager tbody tr" elements
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
