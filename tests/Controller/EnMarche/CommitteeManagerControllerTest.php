<?php

namespace Tests\App\Controller\EnMarche;

use App\Committee\CommitteeManager;
use App\Entity\CommitteeMembership;
use App\Repository\CommitteeMembershipRepository;
use App\Repository\Event\EventRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('committeeManager')]
class CommitteeManagerControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    /* @var EventRepository */
    private $eventRepository;

    /* @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;

    public function testCommitteeFollowerIsNotAllowedToEditCommitteeInformation()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request('GET', \sprintf('%s/editer', $this->client->getRequest()->getPathInfo()));

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    #[DataProvider('provideFollowerCredentials')]
    public function testAuthenticatedFollowerCannotSeeCommitteeMembers(string $username)
    {
        // Authenticate as a committee follower
        $this->authenticateAsAdherent($this->client, $username);
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $this->client->request(Request::METHOD_GET, \sprintf('%s/membres', $this->client->getRequest()->getPathInfo()));

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public static function provideFollowerCredentials(): array
    {
        return [
            'follower 1' => ['carl999@example.fr'],
            'follower 2' => ['luciole1989@spambox.fr'],
        ];
    }

    public function testAuthenticatedProvisionalSupervisorCanSeeCommitteeMembers()
    {
        // Authenticate as the committee provisional supervisor
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-comite-de-evry/membres');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->seeMembersList($crawler, 6);
        self::assertSame('Adrien P.', trim($crawler->filter('.member-name')->eq(0)->text()));
        self::assertCount(0, $crawler->filter('.member-name img.b__nudge--left-nano'));
        self::assertCount(5, $crawler->filter('.member-phone'));
        self::assertSame('77000', $crawler->filter('.member-postal-code')->eq(0)->text());
        self::assertSame('Melun', $crawler->filter('.member-city-name')->eq(0)->text());
        self::assertCount(5, $crawler->filter('.member-status .em-tooltip'));
        self::assertSame('Abonné Email', $crawler->filter('.member-status .em-tooltip .em-tooltip--content p')->eq(0)->text());
    }

    public function testAuthenticatedSupervisorCanFilterCommitteeMembers()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/comites/en-marche-paris-8/membres');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(3, $crawler->filter('table tbody tr'));
        $this->assertCount(1, $crawler->filter('.filter__row:contains("Certifié")'));
        $this->assertCount(1, $crawler->filter('.filter__row:contains("A choisi son comité de vote")'));

        // filter by gender
        $crawler = $this->client->submit($crawler->selectButton('Filtrer')->form([
            'filter[gender]' => 'female',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('table tbody tr'));
        $this->assertSame('Lucie Olivera', trim($crawler->filter('.member-name')->eq(0)->text()));

        // filter by subscribed
        $crawler = $this->client->submit($crawler->selectButton('Filtrer')->form([
            'filter[gender]' => '',
            'filter[subscribed]' => 1,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(2, $crawler->filter('table tbody tr'));
        $this->assertSame('Jacques Picard', trim($crawler->filter('.member-name')->eq(0)->text()));
        $this->assertSame('Lucie Olivera', trim($crawler->filter('.member-name')->eq(1)->text()));

        // filter by certified
        $crawler = $this->client->submit($crawler->selectButton('Filtrer')->form([
            'filter[subscribed]' => '',
            'filter[certified]' => 1,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('table tbody tr'));
        $this->assertSame('Jacques Picard', trim($crawler->filter('.member-name')->eq(0)->text()));

        // filter by votersOnly
        $crawler = $this->client->submit($crawler->selectButton('Filtrer')->form([
            'filter[votersOnly]' => 1,
            'filter[subscribed]' => '',
            'filter[certified]' => '',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('table tbody tr'));
        $this->assertSame('Jacques Picard', trim($crawler->filter('.member-name')->eq(0)->text()));
    }

    public function testAuthenticatedSupervisorCanSeeMoreInfoAboutCommitteeMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->seeMembersList($crawler, 4);
        self::assertSame('Jacques Picard', trim($crawler->filter('.member-name')->eq(0)->text()));
        self::assertCount(1, $crawler->filter('.member-name img.b__nudge--left-nano'));
        self::assertSame('+33 1 87 26 42 36', trim($crawler->filter('.member-phone')->eq(0)->text()));
        self::assertSame('75008', $crawler->filter('.member-postal-code')->eq(0)->text());
        self::assertSame('Paris 8ème', $crawler->filter('.member-city-name')->eq(0)->text());
        self::assertSame('12/01/2017', $crawler->filter('.member-subscription-date')->eq(0)->text());
        self::assertCount(6, $crawler->filter('.member-status .em-tooltip'));
        self::assertSame('Vote dans ce comité', $crawler->filter('.member-status .em-tooltip .em-tooltip--content p')->eq(0)->text());
        self::assertSame('Abonné Email', $crawler->filter('.member-status .em-tooltip .em-tooltip--content p')->eq(1)->text());
    }

    public function testAuthenticatedCommitteeSupervisorCanPromoteNewHostsAmongMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame(2, $crawler->filter('.promote-host-link')->count());
        $this->assertSame(0, $crawler->filter('.demote-host-link')->count());

        $crawler = $this->client->click($crawler->filter('.promote-host-link')->link());

        $this->client->submit($crawler->selectButton("Oui, promouvoir l'adhérent")->form());
        $crawler = $this->client->followRedirect();

        // no more available places for a new host, that's why no link to promote
        $this->assertSame(1, $crawler->filter('.promote-host-link')->count());
        $this->assertSame(1, $crawler->filter('.demote-host-link')->count());
        $this->seeFlashMessage($crawler, 'Le membre a été promu animateur du comité avec succès.');

        $crawler = $this->client->click($crawler->filter('.demote-host-link')->link());

        $this->client->submit($crawler->selectButton('Oui, définir comme simple membre')->form());
        $crawler = $this->client->followRedirect();

        $this->assertSame(2, $crawler->filter('.promote-host-link')->count());
        $this->assertSame(0, $crawler->filter('.demote-host-link')->count());
        $this->seeFlashMessage($crawler, 'Le membre a été redéfini simple membre du comité avec succès.');
    }

    public function testCommitteeExportMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité')->link());
        $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request(Request::METHOD_GET, $this->client->getRequest()->getPathInfo().'?export=1');
        $content = $this->client->getInternalResponse()->getContent();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(4, $this->transformToArray($content));
    }

    public function testAllowToCreateCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'martine.lindt@gmail.com');
        $crawler = $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertStringContainsString('Vous devez être certifiée', $crawler->filter('.committee__warning')->first()->text());

        $this->client->request('POST', '/espace-adherent/creer-mon-comite');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->logout($this->client);

        /** @var CommitteeManager $committeeManager */
        $committeeManager = $this->get(CommitteeManager::class);

        $adherent = $this->getAdherentRepository()->findOneByEmail('martine.lindt@gmail.com');
        $committee = $this->getCommitteeRepository()->findOneByName('En Marche - Comité de Berlin');

        $committeeManager->changePrivilege(
            $adherent,
            $committee,
            CommitteeMembership::COMMITTEE_FOLLOWER
        );

        $this->authenticateAsAdherent($this->client, 'martine.lindt@gmail.com');
        $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');
    }

    private function seeMembersList(Crawler $crawler, int $count): void
    {
        // Header row is part of the count
        self::assertCount($count, $crawler->filter('table tr'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = $this->getEventRepository();
        $this->committeeMembershipRepository = $this->getCommitteeMembershipRepository();

        $this->disableRepublicanSilence();
    }

    protected function tearDown(): void
    {
        $this->committeeMembershipRepository = null;
        $this->eventRepository = null;

        parent::tearDown();
    }
}
