<?php

namespace Tests\AppBundle\Controller\Amp;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group amp
 */
class AmpControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testArticlePublished()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/articles/actualites/outre-mer');

        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertContains('<amp-img src="/assets/images/article.jpg', $this->client->getResponse()->getContent());
    }

    public function testArticleDraft()
    {
        $this->client->request(Request::METHOD_GET, '/articles/actualites/brouillon');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testRedirectionToArticle()
    {
        $this->client = $this->makeClient(false, ['HTTP_HOST' => $this->hosts['app']]);

        $this->client->request(Request::METHOD_GET, '/amp/article/outre-mer');

        $this->assertClientIsRedirectedTo('//'.$this->hosts['amp'].'/articles/actualites/outre-mer', $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }

    public function testRedirectionToProposal()
    {
        $this->client = $this->makeClient(false, ['HTTP_HOST' => $this->hosts['app']]);

        $this->client->request(Request::METHOD_GET, '/amp/proposition/produire-en-france-et-sauver-la-planete');

        $this->assertClientIsRedirectedTo('//'.$this->hosts['amp'].'/proposition/produire-en-france-et-sauver-la-planete', $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }

    public function testRedirectionToOrderArticle()
    {
        $this->client = $this->makeClient(false, ['HTTP_HOST' => $this->hosts['app']]);

        $this->client->request(Request::METHOD_GET, '/amp/transformer-la-france/premiere-article');

        $this->assertClientIsRedirectedTo('//'.$this->hosts['amp'].'/transformer-la-france/premiere-article', $this->client, false, true);

        $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
    }

    public function testProposalPublished()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/proposition/produire-en-france-et-sauver-la-planete');

        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertContains('<amp-img src="/assets/images/proposal.jpg', $this->client->getResponse()->getContent());
    }

    public function testProposalDraft()
    {
        $this->client->request(Request::METHOD_GET, '/proposition/mieux-vivre-de-son-travail');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testOrderArticlePublished()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/transformer-la-france/premiere-article');

        $this->isSuccessful($response = $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('html:contains("An exhibit of Markdown")')->count());
        $this->assertContains('<amp-img src="/assets/images/order_article.jpg', $this->client->getResponse()->getContent());
    }

    public function testOrderArticleDraft()
    {
        $this->client->request(Request::METHOD_GET, '/transformer-la-france/brouillon');

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    public function testSitemap()
    {
        $this->client->request(Request::METHOD_GET, '/sitemap.xml');

        $this->isSuccessful($this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init('amp');
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
