@javascript
@ux-component
Feature:

  Scenario: ReButton (simple text) : should show a simple button with text
    When I go to "/test/ux-component/ReButton/simple"
    Then I should see 1 "button.bg-ui_blue-50" elements
    And I should see "Simple button"

  Scenario: ReButton (icon) : should show a button with icon
    When I go to "/test/ux-component/ReButton/icon"
    Then I should see 1 ".re-icon.re-icon-unlock" elements
    And I should see 1 "button" elements
    And I should see "Simple button"

  Scenario: ReButton (loader-static): should show a button with loader
    When I go to "/test/ux-component/ReButton/loaderstatic"
    Then I should see 1 "button" elements
    And I should not see "Simple button"
    And I should see 1 ".re-icon.re-icon-loading" elements

  Scenario: ReButton (loader-dynamic): should show loader with apline reactive property
    When I go to "/test/ux-component/ReButton/loaderdynamic"
    Then I should see 1 "button" elements
    And I should see "Simple button"
    And I should see 1 ".re-icon.re-icon-loading" elements
    And the '.re-icon' element should not be visible
    When I click the 'button' selector
    Then the '.re-icon' element should be visible
    And I should not see "Simple button"

  Scenario: ReButton (icon + loader) should handle icon and loader together
    When I go to "/test/ux-component/ReButton/iconloader"
    Then I should see 1 "button" elements
    And I should see 1 ".re-icon.re-icon-unlock" elements
    And the '.re-icon-unlock' element should not be visible
    And I should see 1 ".re-icon.re-icon-loading" elements
    And the '.re-icon-unlock' element should not be visible
    And I should not see "Simple button"
