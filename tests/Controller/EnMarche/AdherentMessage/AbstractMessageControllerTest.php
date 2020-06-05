<?php

namespace Tests\App\Controller\EnMarche\AdherentMessage;

use App\Entity\Adherent;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class AbstractMessageControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testICanAccessADelegatedSpace()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/');

        self::assertContains('Espace député délégué', $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());
        $crawler = $this->client->click($crawler->selectLink('Espace député délégué')->link());

        self::assertEquals('http://test.enmarche.code/espace-depute-delegue/messagerie', $crawler->getUri());
        self::assertEquals(0, $crawler->filter('.datagrid__table-manager tbody tr td span.status__2')->count());

        $crawler = $this->client->request('GET', '/espace-depute-delegue/messagerie/creer');
        $this->client->submit($crawler->selectButton('Enregistrer le brouillon')->form(['adherent_message' => [
            'label' => 'test by delegated adherent',
            'subject' => 'subject of delegated message',
            'content' => 'message content of delegated message',
        ]]));
        $crawler = $this->client->followRedirect();

        self::assertEquals('Votre message a bien été créé.', $crawler->filter('.flash--info .flash__inner')->text());

        $this->authenticateAsAdherent($this->client, 'deputy-ch-li@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-depute/messagerie');
        self::assertEquals(1, $crawler->filter('.datagrid__table-manager tbody tr td span.status__2')->count());
        self::assertContains('test by delegated adherent', $crawler->filter('table.datagrid__table-manager')->text());
    }

    public function testICannotSeeTabsIfIHaveNotAccess()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request('GET', '/espace-depute-delegue');
        self::assertCount(2, $crawler->filter('nav.manager-header__menu li'));
        self::assertCount(0, $crawler->filter('nav.manager-header__menu li a:contains("Mes messages")'));
        self::assertCount(0, $crawler->filter('nav.manager-header__menu li a:contains("Comités")'));
        self::assertCount(1, $crawler->filter('nav.manager-header__menu li a:contains("Adhérents")'));
        self::assertCount(1, $crawler->filter('nav.manager-header__menu li a:contains("Événements")'));
    }

    /**
     * @dataProvider provideTabs
     */
    public function testDelegatedAccessWithRestrictedTabs(string $tab, int $statusCode)
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $this->client->request('GET', "/espace-depute-delegue/$tab");
        $this->assertResponseStatusCode($statusCode, $this->client->getResponse());
    }

    public function provideTabs()
    {
        yield ['messagerie', 403];
        yield ['evenements', 200];
        yield ['utilisateurs', 200];
        yield ['comites', 403];
    }

    public function testRemovingDistrictOfDeputyRevokeAccessToDelegates()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/');
        self::assertContains('Espace député délégué', $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());

        $deputy = $this->manager->getRepository(Adherent::class)->findOneByEmail('deputy-ch-li@en-marche-dev.fr');
        $deputy->setManagedDistrict(null);
        $this->manager->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertNotContains('Espace député délégué', $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}
