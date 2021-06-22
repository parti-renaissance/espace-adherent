<?php

namespace Tests\App\Admin;

use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class ArticleCategoryAdminTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCreateCategoryFail()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request('GET', $this->getUrl('admin_app_articlecategory_create'));
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $form = $crawler->selectButton('btn_create_and_edit')->form();
        $crawler = $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        self::assertCount(1, $crawler->filter('div[id*="_slug"].has-error'));
    }

    public function testCreateCategoryWithoutCTASuccess()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request('GET', $this->getUrl('admin_app_articlecategory_create'));
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $form = $crawler->selectButton('btn_create_and_edit')->form();

        $prefix = $this->getFirstPrefixForm($form);
        $form->setValues([
            $prefix.'[name]' => 'Opinion',
            $prefix.'[slug]' => 'opinion',
            $prefix.'[position]' => 10,
        ]);
        $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
    }

    public function testCreateCategoryWithCTASuccess()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request('GET', $this->getUrl('admin_app_articlecategory_create'));
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $form = $crawler->selectButton('btn_create_and_edit')->form();

        $prefix = $this->getFirstPrefixForm($form);
        $form->setValues([
            $prefix.'[name]' => 'Opinion',
            $prefix.'[slug]' => 'opinion',
            $prefix.'[position]' => 10,
            $prefix.'[ctaLink]' => 'http://www.google.fr',
            $prefix.'[ctaLabel]' => 'Google link',
        ]);
        $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
    }

    public function testCreateCategoryWithCTAFail()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request('GET', $this->getUrl('admin_app_articlecategory_create'));
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $form = $crawler->selectButton('btn_create_and_edit')->form();

        $prefix = $this->getFirstPrefixForm($form);
        $form->setValues([
            $prefix.'[name]' => 'Opinion',
            $prefix.'[slug]' => 'opinion',
            $prefix.'[position]' => 10,
            $prefix.'[ctaLink]' => 'not a link',
            $prefix.'[ctaLabel]' => 'Google link',
        ]);
        $crawler = $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        self::assertCount(1, $crawler->filter('div[id*="_ctaLink"].has-error'));
    }
}
