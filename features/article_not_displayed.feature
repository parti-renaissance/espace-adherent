Feature: Be able to have some categories not displayed

  Scenario: Not display categories and articles linked in /aticles
    Given I am on "/articles"
    Then I should not see "Not displayed"
    And I should not see "Article dans une category pas affichée"
    But I am on "/articles/nodisplay/article-avec-category-non-afficher"
    And the response status code should be 200
    And I should see "Article dans une category pas affichée"
