Feature:
  As a user
  In order to confirm i created a citizen project
  I should receive an email from the application

  Background:
    Given the following fixtures are loaded:
      | LoadCitizenProjectData |

  Scenario: Create a citizen project
    Given I am logged as "carl999@example.fr"
    And I am on "/espace-adherent/creer-mon-projet-citoyen"
    And I fill in the following:
      | citizen_project[name]                | Projet citoyen #27      |
      | citizen_project[subtitle]            | Sous-titre #27          |
      | citizen_project[problem_description] | Description du problème |
      | citizen_project[proposed_solution]   | Solution proposée       |
      | citizen_project[required_means]      | Plan d'action           |
      | citizen_project[address][postalCode] | 8802                    |
      | citizen_project[address][cityName]   | Kilchberg               |
      | citizen_project[address][country]    | CH                      |
      | citizen_project[phone][country]      | FR                      |
      | citizen_project[phone][number]       | 0612345678              |
    And I select "Culture" from "citizen_project[category]"
    And I check "citizen_project[cgu]"
    And I check "citizen_project[data_processing]"
    When I press "Proposer mon projet"
    Then I should have 1 email "CitizenProjectCreationConfirmationMessage" for "carl999@example.fr" with payload:
    """
    {
      "FromEmail": "projetscitoyens@en-marche.fr",
      "FromName": "En Marche !",
      "Subject": "Nous avons bien re\u00e7u votre demande de cr\u00e9ation de projet citoyen !",
      "MJ-TemplateID": "244426",
      "MJ-TemplateLanguage": true,
      "Recipients": [
        {
          "Email": "carl999@example.fr",
          "Name": "Carl Mirabeau",
          "Vars": {
            "target_firstname": "Carl",
            "citizen_project_name": "Projet citoyen #27"
          }
        }
      ]
    }
    """
