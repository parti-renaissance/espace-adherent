<?php

namespace Tests\AppBundle\Controller\Admin;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use Symfony\Component\HttpFoundation\Request;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

/**
 * @group functional
 * @group admin
 */
class AdherentAdminTest extends WebTestCase
{
    use ControllerTestTrait;

    private $adherentRepository;

    public function testAnAdminCantBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client);

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/admin/app/adherent/%s/edit', $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Afficher', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(2)->nodeValue));

        $this->client->request(Request::METHOD_GET, sprintf('/admin/adherent/%s/ban', $adherent->getId()));
        $this->assertResponseStatusCode(404, $this->client->getResponse());
    }

    public function testASuperAdminCanBanAnAdherent()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $this->client->followRedirects();

        /** @var Adherent $adherent */
        $adherent = $this->adherentRepository->findOneByUuid(LoadAdherentData::ADHERENT_1_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/admin/app/adherent/%s/edit', $adherent->getId()));

        $navBar = $crawler->filter('ul.dropdown-menu > li');
        $this->assertEquals('Afficher', trim($navBar->getNode(0)->nodeValue));
        $this->assertEquals('Retourner à la liste', trim($navBar->getNode(1)->nodeValue));
        $this->assertEquals('Impersonnifier', trim($navBar->getNode(2)->nodeValue));
        $this->assertEquals('Exclure cet adhérent ⚠️', trim($navBar->getNode(3)->nodeValue));

        $link = $crawler->selectLink('Exclure cet adhérent ⚠️')->link();
        $crawler = $this->client->click($link);

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Confirmer')->form());

        $this->assertContains(sprintf('L\'adhérent <b>%s</b> a bien été exclu', $adherent->getFullName()), $this->client->getResponse()->getContent());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->adherentRepository = $this->getAdherentRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->adherentRepository = null;

        parent::tearDown();
    }
}
