@app
@renaissance
Feature: Test donation page
    In order to see donation as a user
    I should be able to see my donation in my account profile

    Scenario: Be able to navigate in my donation page as an adherent with monthly donations
        Given I am logged as "president-ad@renaissance-dev.fr"
        When I am on "/espace-adherent/mes-dons"
        Then I should see "Don mensuel"
        And I should see "Prochaine échéance du don mensuel de 100 €"

        When I follow "Mettre fin à mon don mensuel"
        Then I should be on "/don/mensuel/annuler"
        And I should see "Êtes-vous sûr(e) de vouloir arrêter votre don mensuel"

        When I press "Non"
        Then I should be on "/espace-adherent/mes-dons"
