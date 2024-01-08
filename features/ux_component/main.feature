@javascript
@ux-component
Feature:
    Scenario Outline: Test UX components
        When I go to "/test/ux-component/<component>?<attributes>"
        Then I should see "UX Component : <component>"
        And I should see "<expected>"
    Examples:
        | component        | attributes                                                                                                                                             | expected    |
        | ReButton         | value=Sauvegarder&color=blue&class=w-full&x-bind:class={disabled:!checkValidity()}&x-on:click=handleOnSubmit($event,dispatch)&xSyncLoading=loading     | Sauvegarder |
        | ReCard           | content=<h1>Bonjour</h1>                                                                                                                               | Bonjour     |
