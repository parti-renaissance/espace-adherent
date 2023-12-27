@javascript
@ux-component
Feature:
    Scenario: Test UX components : ReButton
        When I go to "/test/ux-component/ReButton?value=Sauvegarder&color=blue&class=w-full&x-bind:class={disabled:!checkValidity()}&x-on:click=handleOnSubmit($event,dispatch)&xSyncLoading=loading"
        Then I should see "UX Component : ReButton"
        And I should see "Sauvegarder"
