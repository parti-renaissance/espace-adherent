<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Mailjet\Message\BoardMemberMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class BoardMemberControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    public function testUnothorizeToAccessOnBoardMemberArea()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/profils-sauvegardes');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testIndexBoardMember()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Referent', $crawler->filter('h1')->text());
    }

    public function testSearchBoardMember()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testSavedProfilBoardMember()
    {
        $this->authenticateAsAdherent($this->client, 'kiroule.p@blabla.tld', 'politique2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/profils-sauvegardes');
        $li = $crawler->filter('main ul li');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(3, $li->count());
        $this->assertSame('Laura Deloche', $li->first()->filter('p')->first()->text());
        $this->assertSame('44, F, Rouen', $li->first()->filter('p')->eq(1)->text());
        $this->assertSame('Martine Lindt', $li->eq(1)->filter('p')->first()->text());
        $this->assertSame('16, F, Berlin', $li->eq(1)->filter('p')->eq(1)->text());
        $this->assertSame('Élodie Dutemps', $li->eq(2)->filter('p')->first()->text());
        $this->assertSame('15, F, Singapour', $li->eq(2)->filter('p')->eq(1)->text());
        $this->assertSame('3 profils sauvegardés', $crawler->filter('h2')->first()->text());
    }

    public function testSendMessageToSearchResult()
    {
        $this->authenticateAsBoardMember();

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Rechercher')->form(['g' => 'male']));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->click($this->client->getCrawler()->selectLink('Envoyer un message à ces 2 personnes')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form([
            'board_member_message' => [
                'subject' => 'Sujet',
                'content' => 'Message from search',
            ],
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertCount(1, $this->getMailjetEmailRepository()->findMessages(BoardMemberMessage::class));
        $this->assertCountMails(1, BoardMemberMessage::class, 'referent@en-marche-dev.fr');
        $this->assertCountMails(1, BoardMemberMessage::class, 'kiroule.p@blabla.tld');
    }

    public function testSendMessageToSavedMembers()
    {
        $this->authenticateAsBoardMember();

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/profils-sauvegardes');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->click($this->client->getCrawler()->selectLink('Envoyer un message à ces 3 personnes')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form([
            'board_member_message' => [
                'subject' => 'Sujet',
                'content' => 'Message for saved members',
            ],
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertCount(1, $this->getMailjetEmailRepository()->findMessages(BoardMemberMessage::class));
        $this->assertCountMails(1, BoardMemberMessage::class, 'laura@deloche.com');
        $this->assertCountMails(1, BoardMemberMessage::class, 'lolodie.dutemps@hotnix.tld');
        $this->assertCountMails(1, BoardMemberMessage::class, 'martine.lindt@gmail.com');
    }

    public function testSendMessageToMember()
    {
        $this->authenticateAsBoardMember();

        $this->client->request(Request::METHOD_GET, '/espace-membres-conseil/recherche');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->click($this->client->getCrawler()->filter('main ul li')->selectLink('Envoyer un message')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertContains('à un membre du conseil', $this->client->getResponse()->getContent());
        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form([
            'board_member_message' => [
                'subject' => 'Sujet',
                'content' => 'Message for one member',
            ],
        ]));
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $this->assertCount(1, $this->getMailjetEmailRepository()->findMessages(BoardMemberMessage::class));
        $this->assertCountMails(1, BoardMemberMessage::class, 'referent@en-marche-dev.fr');
    }

    private function authenticateAsBoardMember()
    {
        $this->authenticateAsAdherent($this->client, 'kiroule.p@blabla.tld', 'politique2017');
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
