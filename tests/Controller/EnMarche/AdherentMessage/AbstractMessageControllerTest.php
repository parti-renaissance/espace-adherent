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

    /**
     * @dataProvider provideSpaces
     */
    public function testDifferentSpaceCanBeDelegated(string $email, string $spaceLabel, string $spaceSlug, string $tab)
    {
        $this->authenticateAsAdherent($this->client, $email);

        $crawler = $this->client->request('GET', '/');
        self::assertContains($spaceLabel, $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());

        $crawler = $this->client->click($crawler->selectLink($spaceLabel)->link());
        $this->assertResponseStatusCode(200, $this->client->getResponse());

        self::assertRegExp('#http://test.enmarche.code/'.$spaceSlug.'/.+/'.$tab.'#', $crawler->getUri());
    }

    public function provideSpaces()
    {
        yield ['referent@en-marche-dev.fr', 'Espace député partagé (FDE-06)', 'espace-depute-delegue', 'messagerie'];
        yield ['gisele-berthoux@caramail.com', 'Espace sénateur partagé (59)', 'espace-senateur-delegue', 'messagerie'];
        yield ['gisele-berthoux@caramail.com', 'Espace député partagé (FDE-06)', 'espace-depute-delegue', 'messagerie'];
        yield ['gisele-berthoux@caramail.com', 'Espace député partagé (75002)', 'espace-depute-delegue', 'utilisateurs'];
    }

    public function testICanAccessADelegatedSpace()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/');

        self::assertContains('Espace député partagé (FDE-06)', $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());
        $crawler = $this->client->click($crawler->selectLink('Espace député partagé (FDE-06)')->link());

        self::assertEquals('http://test.enmarche.code/espace-depute-delegue/2e80d106-4bcb-4b28-97c9-3856fc235b27/messagerie', $crawler->getUri());
        self::assertEquals(0, $crawler->filter('.datagrid__table-manager tbody tr td span.status__2')->count());

        $crawler = $this->client->request('GET', '/espace-depute-delegue/2e80d106-4bcb-4b28-97c9-3856fc235b27/messagerie/creer');
        $this->client->submit($crawler->selectButton('Enregistrer le brouillon')->form(['adherent_message' => [
            'label' => 'test by delegated adherent',
            'subject' => 'subject of delegated message',
            'content' => 'message content of delegated message',
        ]]));
        $crawler = $this->client->followRedirect();

        self::assertEquals('Votre message a bien été créé.', $crawler->filter('.flash--info .flash__inner')->text());

        $this->getEntityManager(Adherent::class)->clear();
        $this->authenticateAsAdherent($this->client, 'deputy-ch-li@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-depute/messagerie');
        self::assertEquals(1, $crawler->filter('.datagrid__table-manager tbody tr td span.status__2')->count());
        self::assertContains('test by delegated adherent', $crawler->filter('table.datagrid__table-manager')->text());
    }

    public function testICannotSeeTabsIfIHaveNotAccess()
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request('GET', '/espace-depute-delegue/f4ce89da-1272-4a01-a47e-4ce5248ce018/evenements');
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

        $this->client->request('GET', "/espace-depute-delegue/f4ce89da-1272-4a01-a47e-4ce5248ce018/$tab");
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
        self::assertContains('Espace député partagé (FDE-06)', $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());

        $this->logout($this->client);
        $this->getEntityManager(Adherent::class)->clear();

        $deputy = $this->manager->getRepository(Adherent::class)->findOneByEmail('deputy-ch-li@en-marche-dev.fr');
        $deputy->setManagedDistrict(null);
        $this->manager->flush();

        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/');
        self::assertNotContains('Espace député partagé (FDE-06)', $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());
    }

    /**
     * @dataProvider provideMultiAccess
     */
    public function testIHaveMultipleAccesses(string $path, int $statusCode)
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $this->client->request('GET', $path);
        $this->assertResponseStatusCode($statusCode, $this->client->getResponse());
    }

    public function provideMultiAccess()
    {
        yield ['/espace-depute-delegue/96076afb-2243-4251-97fe-8201d50c3256/utilisateurs', 403];
        yield ['/espace-depute-delegue/96076afb-2243-4251-97fe-8201d50c3256/messagerie', 200];
        yield ['/espace-senateur-delegue/411faa64-202d-4ff2-91ce-c98b29af28ef/utilisateurs', 200];
        yield ['/espace-senateur-delegue/411faa64-202d-4ff2-91ce-c98b29af28ef/messagerie', 200];
        yield ['/espace-depute-delegue/d2315289-a3fd-419c-a3dd-3e1ff71b754d/utilisateurs', 200];
        yield ['/espace-depute-delegue/d2315289-a3fd-419c-a3dd-3e1ff71b754d/messagerie', 403];
    }

    public function testADelegatedAdherentCanHaveNoAccesses()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/');
        self::assertNotContains('Espace délégué du sénateur Bob Senateur (59)', $crawler->filter('.nav-dropdown__menu > ul.list__links')->text());
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
