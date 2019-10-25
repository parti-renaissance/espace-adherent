Feature:
  In order to use the website
  I should be able to navigate through the nav

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData  |
      | LoadArticleData   |
      | LoadEventData     |
      | LoadHomeBlockData |
      | LoadPageData      |

  Scenario: The global sitemap displays correctly
    Given I am on "/sitemap.xml"
    Then the response should be in XML
    And the XML element "//sitemapindex" should have 6 element
    And the XML element "//sitemap[1]/loc" should be equal to "http://test.enmarche.code/sitemap_main_1.xml"
    And the XML element "//sitemap[2]/loc" should be equal to "http://test.enmarche.code/sitemap_content_1.xml"
    And the XML element "//sitemap[3]/loc" should be equal to "http://test.enmarche.code/sitemap_images_1.xml"
    And the XML element "//sitemap[4]/loc" should be equal to "http://test.enmarche.code/sitemap_committees_1.xml"
    And the XML element "//sitemap[5]/loc" should be equal to "http://test.enmarche.code/sitemap_events_1.xml"
    And the XML element "//sitemap[6]/loc" should be equal to "http://test.m.enmarche.code/sitemap.xml"

  Scenario: The main sitemap displays correctly
    Given I am on "/sitemap_main_1.xml"
    Then the response should be in XML
    And the XML element "//urlset" should have 5 element
    And the XML element "//url[1]/loc" should be equal to "http://test.enmarche.code/"
    And the XML element "//url[2]/loc" should be equal to "http://test.enmarche.code/don"
    And the XML element "//url[3]/loc" should be equal to "http://test.enmarche.code/jagis"
    And the XML element "//url[4]/loc" should be equal to "http://test.enmarche.code/newsletter"
    And the XML element "//url[5]/loc" should be equal to "http://test.enmarche.code/invitation"

  Scenario: The content sitemap displays correctly
    Given I am on "/sitemap_content_1.xml"
    Then the response status code should be 200
    And the response should be in XML
    And the XML element "//urlset" should have 213 element

  Scenario: The images sitemap displays correctly
    Given I am on "/sitemap_images_1.xml"
    Then the response should be in XML
    And the XML element "//urlset" should have 9 element

  Scenario: The committees sitemap displays correctly
    Given I am on "/sitemap_committees_1.xml"
    Then the response should be in XML
    And the XML element "//urlset" should have 9 element
    And the XML element "//url[1]/loc" should be equal to "http://test.enmarche.code/comites/en-marche-paris-8"
