@api
@group04
Feature:
  In order to manage email templates
  As client software developer
  I should be able to access API email templates

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData      |
      | LoadClientData        |
      | LoadEmailTemplateData |

  Scenario: I can get a logged-in user templates
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/email_templates"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node metadata.total_items should be equal to 1
    And the JSON node "items[0].label" should be equal to the string "Test Template Email"

  Scenario: I can get a specific email template for logged-in user
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/email_templates/7fc776c1-ead9-46cc-ada8-2601c49b5312"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "label": "Test Template Email",
      "content": "@string@",
      "uuid": "7fc776c1-ead9-46cc-ada8-2601c49b5312",
      "created_at": "@string@.isDateTime()"
    }
    """

  Scenario: I can create a new email template
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/email_templates" with body:
    """
    {
      "label": "Information mail",
      "content": "<table class=\"main-body\" style=\"box-sizing: border-box; min-height: 150px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; width: 100%; height: 100%; background-color: rgb(234, 236, 237);\" width=\"100%\" height=\"100%\" bgcolor=\"rgb(234, 236, 237)\">\n  <tbody style=\"box-sizing: border-box;\">\n    <tr class=\"row\" style=\"box-sizing: border-box; vertical-align: top;\" valign=\"top\">\n      <td class=\"main-body-cell\" style=\"box-sizing: border-box;\">\n        <table class=\"container\" style=\"box-sizing: border-box; font-family: Helvetica, serif; min-height: 150px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; margin-top: auto; margin-right: auto; margin-bottom: auto; margin-left: auto; height: 0px; width: 90%; max-width: 550px;\" width=\"90%\" height=\"0\">\n          <tbody style=\"box-sizing: border-box;\">\n            <tr style=\"box-sizing: border-box;\">\n              <td class=\"container-cell\" style=\"box-sizing: border-box; vertical-align: top; font-size: medium; padding-bottom: 50px;\" valign=\"top\">\n\n                <table class=\"c1766\" style=\"box-sizing: border-box; margin-top: 0px; margin-right: auto; margin-bottom: 10px; margin-left: 0px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; width: 100%; min-height: 30px;\" width=\"100%\">\n                  <tbody style=\"box-sizing: border-box;\">\n                    <tr style=\"box-sizing: border-box;\">\n                      <td class=\"cell c1769\" style=\"box-sizing: border-box; width: 11%;\" width=\"11%\">\n                        <img src=\"//artf.github.io/grapesjs/img/grapesjs-logo.png\" alt=\"GrapesJS.\" class=\"c926\" style=\"box-sizing: border-box; color: rgb(158, 83, 129); width: 100%; font-size: 50px;\">\n                      </td>\n                      <td class=\"cell c1776\" style=\"box-sizing: border-box; width: 70%; vertical-align: middle;\" width=\"70%\" valign=\"middle\">\n                        <div class=\"c1144\" style=\"box-sizing: border-box; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; font-size: 17px; font-weight: 300;\">GrapesJS Newsletter Builder\n                        </div>\n                      </td>\n                    </tr>\n                  </tbody>\n                </table>\n                <table class=\"card\" style=\"box-sizing: border-box; min-height: 150px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; margin-bottom: 20px; height: 0px;\" height=\"0\">\n                  <tbody style=\"box-sizing: border-box;\">\n                    <tr style=\"box-sizing: border-box;\">\n                      <td class=\"card-cell\" style=\"box-sizing: border-box; background-color: rgb(255, 255, 255); overflow-x: hidden; overflow-y: hidden; border-top-left-radius: 3px; border-top-right-radius: 3px; border-bottom-right-radius: 3px; border-bottom-left-radius: 3px; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px; text-align: center;\" bgcolor=\"rgb(255, 255, 255)\" align=\"center\">\n                        <table class=\"table100 c1357\" style=\"box-sizing: border-box; width: 100%; min-height: 150px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; height: 0px; margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; border-collapse: collapse;\" width=\"100%\" height=\"0\">\n                          <tbody style=\"box-sizing: border-box;\">\n                            <tr style=\"box-sizing: border-box;\">\n                              <td class=\"card-content\" style=\"box-sizing: border-box; font-size: 13px; line-height: 20px; color: rgb(111, 119, 125); padding-top: 10px; padding-right: 20px; padding-bottom: 0px; padding-left: 20px; vertical-align: top;\" valign=\"top\">\n                                <h1 class=\"card-title\" style=\"box-sizing: border-box; font-size: 25px; font-weight: 300; color: rgb(68, 68, 68);\">Build your newsletters faster than ever\n                                  <br style=\"box-sizing: border-box;\">\n                                </h1>\n                                <p class=\"card-text\" style=\"box-sizing: border-box;\">Import, build, test and export responsive newsletter templates faster than ever using the GrapesJS Newsletter Builder.\n                                </p>\n                                <table class=\"c1542\" style=\"box-sizing: border-box; margin-top: 0px; margin-right: auto; margin-bottom: 10px; margin-left: auto; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; width: 100%;\" width=\"100%\">\n                                  <tbody style=\"box-sizing: border-box;\">\n                                    <tr style=\"box-sizing: border-box;\">\n                                      <td id=\"c1545\" class=\"card-footer\" style=\"box-sizing: border-box; padding-top: 20px; padding-right: 0px; padding-bottom: 20px; padding-left: 0px; text-align: center;\" align=\"center\">\n                                        <a href=\"https://github.com/artf/grapesjs\" class=\"button\" style=\"box-sizing: border-box; font-size: 12px; padding-top: 10px; padding-right: 20px; padding-bottom: 10px; padding-left: 20px; background-color: rgb(217, 131, 166); color: rgb(255, 255, 255); text-align: center; border-top-left-radius: 3px; border-top-right-radius: 3px; border-bottom-right-radius: 3px; border-bottom-left-radius: 3px; font-weight: 300;\">Free and Open Source\n                                        </a>\n                                      </td>\n                                    </tr>\n                                  </tbody>\n                                </table>\n                              </td>\n                            </tr>\n                          </tbody>\n                        </table>\n                      </td>\n                    </tr>\n                  </tbody>\n                </table>\n              </td>\n            </tr>\n          </tbody>\n        </table>\n      </td>\n    </tr>\n  </tbody>\n</table>"
    }
    """
    Then the response status code should be 201
    And the JSON should be equal to:
    """
    {
      "label": "Information mail",
      "content": "@string@",
      "uuid": "@string@",
      "created_at": "@string@.isDateTime()"
    }
    """

  Scenario: I can update a logged-in user email template with a specific uuid
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMarche App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/email_templates/7fc776c1-ead9-46cc-ada8-2601c49b5312" with body:
    """
    {
      "label": "Updated Test Template Email",
      "content": "<table class=\"main-body\" style=\"box-sizing: border-box; min-height: 150px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; width: 100%; height: 100%; background-color: rgb(234, 236, 237);\" width=\"100%\" height=\"100%\" bgcolor=\"rgb(234, 236, 237)\">\n  <tbody style=\"box-sizing: border-box;\">\n    <tr class=\"row\" style=\"box-sizing: border-box; vertical-align: top;\" valign=\"top\">\n      <td class=\"main-body-cell\" style=\"box-sizing: border-box;\">\n        <table class=\"container\" style=\"box-sizing: border-box; font-family: Helvetica, serif; min-height: 150px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; margin-top: auto; margin-right: auto; margin-bottom: auto; margin-left: auto; height: 0px; width: 90%; max-width: 550px;\" width=\"90%\" height=\"0\">\n          <tbody style=\"box-sizing: border-box;\">\n            <tr style=\"box-sizing: border-box;\">\n              <td class=\"container-cell\" style=\"box-sizing: border-box; vertical-align: top; font-size: medium; padding-bottom: 50px;\" valign=\"top\">\n\n                <table class=\"c1766\" style=\"box-sizing: border-box; margin-top: 0px; margin-right: auto; margin-bottom: 10px; margin-left: 0px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; width: 100%; min-height: 30px;\" width=\"100%\">\n                  <tbody style=\"box-sizing: border-box;\">\n                    <tr style=\"box-sizing: border-box;\">\n                      <td class=\"cell c1769\" style=\"box-sizing: border-box; width: 11%;\" width=\"11%\">\n                        <img src=\"//artf.github.io/grapesjs/img/grapesjs-logo.png\" alt=\"GrapesJS.\" class=\"c926\" style=\"box-sizing: border-box; color: rgb(158, 83, 129); width: 100%; font-size: 50px;\">\n                      </td>\n                      <td class=\"cell c1776\" style=\"box-sizing: border-box; width: 70%; vertical-align: middle;\" width=\"70%\" valign=\"middle\">\n                        <div class=\"c1144\" style=\"box-sizing: border-box; padding-top: 10px; padding-right: 10px; padding-bottom: 10px; padding-left: 10px; font-size: 17px; font-weight: 300;\">GrapesJS Newsletter Builder\n                        </div>\n                      </td>\n                    </tr>\n                  </tbody>\n                </table>\n                <table class=\"card\" style=\"box-sizing: border-box; min-height: 150px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; margin-bottom: 20px; height: 0px;\" height=\"0\">\n                  <tbody style=\"box-sizing: border-box;\">\n                    <tr style=\"box-sizing: border-box;\">\n                      <td class=\"card-cell\" style=\"box-sizing: border-box; background-color: rgb(255, 255, 255); overflow-x: hidden; overflow-y: hidden; border-top-left-radius: 3px; border-top-right-radius: 3px; border-bottom-right-radius: 3px; border-bottom-left-radius: 3px; padding-top: 0px; padding-right: 0px; padding-bottom: 0px; padding-left: 0px; text-align: center;\" bgcolor=\"rgb(255, 255, 255)\" align=\"center\">\n                        <table class=\"table100 c1357\" style=\"box-sizing: border-box; width: 100%; min-height: 150px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; height: 0px; margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px; border-collapse: collapse;\" width=\"100%\" height=\"0\">\n                          <tbody style=\"box-sizing: border-box;\">\n                            <tr style=\"box-sizing: border-box;\">\n                              <td class=\"card-content\" style=\"box-sizing: border-box; font-size: 13px; line-height: 20px; color: rgb(111, 119, 125); padding-top: 10px; padding-right: 20px; padding-bottom: 0px; padding-left: 20px; vertical-align: top;\" valign=\"top\">\n                                <h1 class=\"card-title\" style=\"box-sizing: border-box; font-size: 25px; font-weight: 300; color: rgb(68, 68, 68);\">Build your newsletters faster than ever\n                                  <br style=\"box-sizing: border-box;\">\n                                </h1>\n                                <p class=\"card-text\" style=\"box-sizing: border-box;\">Import, build, test and export responsive newsletter templates faster than ever using the GrapesJS Newsletter Builder.\n                                </p>\n                                <table class=\"c1542\" style=\"box-sizing: border-box; margin-top: 0px; margin-right: auto; margin-bottom: 10px; margin-left: auto; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; width: 100%;\" width=\"100%\">\n                                  <tbody style=\"box-sizing: border-box;\">\n                                    <tr style=\"box-sizing: border-box;\">\n                                      <td id=\"c1545\" class=\"card-footer\" style=\"box-sizing: border-box; padding-top: 20px; padding-right: 0px; padding-bottom: 20px; padding-left: 0px; text-align: center;\" align=\"center\">\n                                        <a href=\"https://github.com/artf/grapesjs\" class=\"button\" style=\"box-sizing: border-box; font-size: 12px; padding-top: 10px; padding-right: 20px; padding-bottom: 10px; padding-left: 20px; background-color: rgb(217, 131, 166); color: rgb(255, 255, 255); text-align: center; border-top-left-radius: 3px; border-top-right-radius: 3px; border-bottom-right-radius: 3px; border-bottom-left-radius: 3px; font-weight: 300;\">Free and Open Source\n                                        </a>\n                                      </td>\n                                    </tr>\n                                  </tbody>\n                                </table>\n                              </td>\n                            </tr>\n                          </tbody>\n                        </table>\n                      </td>\n                    </tr>\n                  </tbody>\n                </table>\n              </td>\n            </tr>\n          </tbody>\n        </table>\n      </td>\n    </tr>\n  </tbody>\n</table>"
    }
    """
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "label": "Updated Test Template Email",
      "content": "@string@",
      "uuid": "7fc776c1-ead9-46cc-ada8-2601c49b5312",
      "created_at": "@string@.isDateTime()"
    }
    """

  Scenario: I can delete an email template
    Given I am logged with "referent@en-marche-dev.fr" via OAuth client "JeMarche App"
    When I send a "DELETE" request to "/api/v3/email_templates/7fc776c1-ead9-46cc-ada8-2601c49b5312"
    Then the response status code should be 204

  Scenario Outline: As a non logged-in user I can not access email templates endpoints
    Given I add "Accept" header equal to "application/json"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method | url                                                          |
      | GET    | /api/v3/email_templates                                      |
      | GET    | /api/v3/email_templates/7fc776c1-ead9-46cc-ada8-2601c49b5312 |
      | POST   | /api/v3/email_templates                                      |
      | PUT    | /api/v3/email_templates/7fc776c1-ead9-46cc-ada8-2601c49b5312 |
      | DELETE | /api/v3/email_templates/7fc776c1-ead9-46cc-ada8-2601c49b5312 |

  Scenario: I can not access email templates data with the wrong roles
    Given I am logged with "carl999@example.fr" via OAuth client "JeMarche App"
    When I send a "GET" request to "/api/v3/email_templates"
    Then the response status code should be 403
