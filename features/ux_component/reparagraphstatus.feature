@javascript
@ux-component
@rep
Feature:

  Scenario: Test UX components : ReParagraphStatus static
    When I go to "/test/ux-component/ReParagraphStatus/static"
    Then I should see "content"
    And I should see 1 "div.re-paragraph-status--error" element

  Scenario: Test UX components : ReParagraphStatus static icon
    When I go to "/test/ux-component/ReParagraphStatus/staticicon"
    Then I should see "content"
    And I should see 1 "span.re-icon-error" element

  Scenario: Test UX components : ReParagraphStatus dynamic
    When I go to "/test/ux-component/ReParagraphStatus/dynamic"
    And I should see 1 "div.re-paragraph-status--error" element
    Then I click the "button" selector
    And I should see 1 "div.re-paragraph-status--warning" element

  Scenario: Test UX components : ReParagraphStatus dynamic-icon
    When I go to "/test/ux-component/ReParagraphStatus/dynamicicon"
    And I should see 1 "span.re-icon-error" element
    Then I click the "button" selector
    And I should see 1 "span.re-icon-warning" element

  Scenario: Test UX components : ReParagraphStatus dynamic-text
    When I go to "/test/ux-component/ReParagraphStatus/dynamictext"
    And I should see "content"
    Then I click the "button" selector
    And I should see "dynamic content"

  Scenario: Test UX components : ReParagraphStatus dynamic-html
    When I go to "/test/ux-component/ReParagraphStatus/dynamichtml"
    And I should see 0 "p.dynamic-content" element
    Then I click the "button" selector
    And I should see 1 "p.dynamic-content" element

  Scenario: Test UX components : ReParagraphStatus slim
    When I go to "/test/ux-component/ReParagraphStatus/slim"
    And I should see 1 "div.re-paragraph-status--slim" element
