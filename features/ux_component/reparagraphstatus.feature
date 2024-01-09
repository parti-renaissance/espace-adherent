@javascript
@ux-component
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
    Then I should see 1 "div.re-paragraph-status--error" element
    When I click the "button" selector
    Then I should see 1 "div.re-paragraph-status--warning" element

  Scenario: Test UX components : ReParagraphStatus dynamic-icon
    When I go to "/test/ux-component/ReParagraphStatus/dynamicicon"
    Then I should see 1 "span.re-icon-error" element
    When I click the "button" selector
    Then I should see 1 "span.re-icon-warning" element

  Scenario: Test UX components : ReParagraphStatus dynamic-text
    When I go to "/test/ux-component/ReParagraphStatus/dynamictext"
    Then I should see "content"
    When I click the "button" selector
    Then I should see "dynamic content"

  Scenario: Test UX components : ReParagraphStatus dynamic-html
    When I go to "/test/ux-component/ReParagraphStatus/dynamichtml"
    Then I should see 0 "p.dynamic-content" element
    When I click the "button" selector
    Then I should see 1 "p.dynamic-content" element

  Scenario: Test UX components : ReParagraphStatus slim
    When I go to "/test/ux-component/ReParagraphStatus/slim"
    Then I should see 1 "div.re-paragraph-status--slim" element
