Feature: Be able to subscribe to newsletter in multiple pages
  In order to receive newsletter
  As an anonymous user
  I need to be able to subscribe to newsletter from Homepage, any article page, ...

  Scenario Outline: In an article page I should be able to subscribe with all correct informations
    Given the following fixtures are loaded:
      | LoadArticleData |
    And I am on "<url>"
    And I should see "<title>"
    When I fill in the following:
      | <email_label> | user-news@newsletter.fr |
      | <zip_label>   | 75001                   |
    And I select "FR" from "app_newsletter_subscription_country"
    And I press "Je m'inscris"
    Then I should be on "/newsletter/merci"
    And I should see "Vous êtes désormais inscrit à notre newsletter !"

    Examples:
      | url                            | title                              | email_label          | zip_label         |
      | /                              | Recevez la newsletter du mouvement | Adresse email        | Code postal       |
      | /newsletter                    | Je m'inscris à la newsletter       | Votre adresse e-mail | Votre code postal |
      | /articles/actualites/outre-mer | Recevez la newsletter              | Adresse email        | Code postal       |

  Scenario: In the newsletter page I should have form error if I submit black field
    Given I am on "/newsletter"
    And I should see "Je m'inscris à la newsletter "
    When I press "Je m'inscris"
    Then I should be on "/newsletter"
    And I should see "Cette valeur est requise."
    And I should see "Veuillez renseigner un code postal."
    And I should see "Cette valeur ne doit pas être vide."

  Scenario: In the newsletter page I should have form error if I submit wrong value
    Given I am on "/newsletter"
    And I should see "Je m'inscris à la newsletter "
    When I fill in the following:
      | Votre adresse e-mail | user-news |
      | Votre code postal    | 75001     |
    And I select "FR" from "app_newsletter_subscription_country"
    And I press "Je m'inscris"
    Then I should be on "/newsletter"
    And I should see "Ceci n'est pas une adresse e-mail valide."
