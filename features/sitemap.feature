Feature:
  In order to use the website
  I should be able to navigate through the nav

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData  |
      | LoadEventData     |
      | LoadPageData      |

  Scenario: The global sitemap references other sitemaps
    Given I am on "/sitemap.xml"
    Then the response should be in XML
    And the XML element "//sitemapindex" should have 7 element
    And the XML element "//sitemap[1]/loc" should be equal to "http://test.enmarche.code/sitemap_main_1.xml"
    And the XML element "//sitemap[2]/loc" should be equal to "http://test.enmarche.code/sitemap_content_1.xml"
    And the XML element "//sitemap[3]/loc" should be equal to "http://test.enmarche.code/sitemap_images_1.xml"
    And the XML element "//sitemap[4]/loc" should be equal to "http://test.enmarche.code/sitemap_videos_1.xml"
    And the XML element "//sitemap[5]/loc" should be equal to "http://test.enmarche.code/sitemap_committees_1.xml"
    And the XML element "//sitemap[6]/loc" should be equal to "http://test.enmarche.code/sitemap_events_1.xml"
    And the XML element "//sitemap[7]/loc" should be equal to "http://test.m.enmarche.code/sitemap.xml"
