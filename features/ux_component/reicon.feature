@javascript
@ux-component
Feature:

    Scenario: Test UX components : ReIcon static
        When I go to "/test/ux-component/ReIcon/static"
        Then I should see 1 ".re-icon.re-icon-unlock" elements
        And I should see 1 "[href='#re-icon-unlock']" elements

    Scenario: Test UX components : ReIcon dynamic
        When I go to "/test/ux-component/ReIcon/dynamic"
        Then I should see 1 ".re-icon.re-icon-arrow-left" elements
        And I should see 1 "[href='#re-icon-arrow']" elements
        When I click the 'button' selector
        Then I should see 1 ".re-icon.re-icon-arrow-right" elements
        And I should see 1 "[href='#re-icon-arrow']" elements
