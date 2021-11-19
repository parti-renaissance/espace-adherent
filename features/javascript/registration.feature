@javascript
Feature:
  As a visitor
  In order to access the web site
  I can register

  Background:
    Given the following fixtures are loaded:
      | LoadUserData        |
      | LoadReferentTagData |

  Scenario: I can become adherent with a foreign country
    And I am logged as "simple-user@example.ch"
    And I am on "/adhesion"
    And I fill in the following:
      | become_adherent[nationality]         | CH                 |
      | become_adherent[address][country]    | CH                 |
      | become_adherent[address][address]    | 32 Zeppelinstrasse |
      | become_adherent[address][postalCode] | 8057               |
      | become_adherent[gender]              | male               |
      | become_adherent[phone][number]       | 06 12 34 56 78     |
      | become_adherent[birthdate][day]      | 1                  |
      | become_adherent[birthdate][month]    | 1                  |
      | become_adherent[birthdate][year]     | 1980               |
    And I click the "field-conditions" element
    And I click the "field-com-email" element
    When I press "Je rejoins La République En Marche"
    Then I should see "Veuillez renseigner une ville."

    Given I fill in the following:
      | become_adherent[address][cityName] | Zürich |
    When I press "Je rejoins La République En Marche"
    Then I should be on "/espace-adherent/accueil"
    And the adherent "simple-user@example.ch" should have the "CH" referent tag
    And I should see "Votre compte adhérent est maintenant actif."

  Scenario: I can become adherent with a french address
    And I am logged as "simple-user@example.ch"
    And I am on "/adhesion"
    And I fill in the following:
      | become_adherent[address][country] | FR                  |
      | become_adherent[nationality]      | FR                  |
      | become_adherent[address][address] | 1 rue des alouettes |
      | become_adherent[gender]           | male                |
      | become_adherent[phone][number]    | 06 12 34 56 78      |
      | become_adherent[birthdate][day]   | 1                   |
      | become_adherent[birthdate][month] | 1                   |
      | become_adherent[birthdate][year]  | 1980                |
    And I click the "field-conditions" element
    When I press "Je rejoins La République En Marche"
    Then I should be on "/adhesion"
    And I should see "Veuillez renseigner une ville."

    Given I fill in the following:
      | become_adherent[address][country]    | FR       |
      | become_adherent[address][postalCode] | 69001    |
    And I wait until I see "Lyon" in the "#become_adherent_address_city" element
    When I press "Je rejoins La République En Marche"
    Then I should be on "/espace-adherent/accueil"
    And I should see "Votre compte adhérent est maintenant actif."
    And the adherent "simple-user@example.ch" should have the "69" referent tag

  Scenario: I can become adherent with a custom gender
    And I am logged as "simple-user@example.ch"
    And I am on "/adhesion"
    And I fill in the following:
      | become_adherent[nationality]         | FR                  |
      | become_adherent[address][country]    | FR                  |
      | become_adherent[address][address]    | 1 rue des alouettes |
      | become_adherent[gender]              | other               |
      | become_adherent[phone][number]       | 06 12 34 56 78      |
      | become_adherent[birthdate][day]      | 1                   |
      | become_adherent[birthdate][month]    | 1                   |
      | become_adherent[birthdate][year]     | 1980                |
      | become_adherent[address][country]    | FR                  |
      | become_adherent[address][postalCode] | 69001               |
    And I click the "field-conditions" element
    When I press "Je rejoins La République En Marche"
    Then I should be on "/adhesion"
    Given I fill in the following:
      | become_adherent[customGender] | Etre non binaire |
    And I wait until I see "Lyon" in the "#become_adherent_address_city" element
    When I press "Je rejoins La République En Marche"
    Then I should be on "/espace-adherent/accueil"
    And I should see "Votre compte adhérent est maintenant actif."
