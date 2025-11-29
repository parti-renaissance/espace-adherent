<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche;

use App\Entity\Committee;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

#[Group('functional')]
#[Group('committee')]
class CommitteeControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    public function testAnonymousUserIsNotAllowedToFollowCommittee()
    {
        $committeeUrl = '/comites/en-marche-dammarie-les-lys';

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
    }

    public function testAuthenticatedCommitteeSupervisorCannotUnfollowCommittee()
    {
        // Login as supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');

        $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());

        self::assertStringNotContainsString('Quitter ce comité', $response->getContent());
    }

    public function testAuthenticatedAdherentCanFollowCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        // Browse to the committee details page
        $committeeUrl = '/comites/en-marche-dammarie-les-lys';

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('1 adhérent', $crawler->filter('.committee__infos')->text());
        $this->assertTrue($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));

        // Emulate POST request to follow the committee.
        $token = $crawler->selectButton('Suivre ce comité')->attr('data-csrf-token');
        $this->client->request(Request::METHOD_POST, $committeeUrl.'/rejoindre', ['token' => $token]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Refresh the committee details page
        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('1 adhérent', $crawler->filter('.committee__infos')->text());
        $this->assertFalse($this->seeFollowLink($crawler));
        $this->assertTrue($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));

        // Emulate POST request to unfollow the committee.
        $token = $crawler->selectButton('Quitter ce comité')->attr('data-csrf-token');
        $this->client->request(Request::METHOD_POST, $committeeUrl.'/quitter', ['token' => $token]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Refresh the committee details page
        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('1 adhérent', $crawler->filter('.committee__infos')->text());
        $this->assertTrue($this->seeFollowLink($crawler));
        $this->assertFalse($this->seeUnfollowLink($crawler));
        $this->assertFalse($this->seeRegisterLink($crawler, 0));
    }

    public function testApprovedCommitteePageIsViewableByAnyone()
    {
        $committeeUrl = '/comites/en-marche-dammarie-les-lys';

        // Anonymous
        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->seeMessageForContactHosts($crawler);

        // Adherent
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->logout($this->client);

        // Member
        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com');

        $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testAnyoneCanSeeSupervisorProvisionalOnCommitteePage()
    {
        $committeeUrl = '/comites/en-marche-comite-de-evry';

        // Anonymous
        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->seeMessageForContactHosts($crawler);
        $this->assertSeeHosts($crawler, [
            ['FB', 'Francis B.', 'Animateur'],
            ['GB', 'Gisele B.', 'Animatrice provisoire'],
        ], false);
    }

    public function testUnapprovedCommitteeIsViewableByItsCreator()
    {
        $committeeUrl = '/comites/en-marche-marseille-3';

        // Adherent
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->logout($this->client);
    }

    public function testAnonymousGuestCanShowCommitteePage()
    {
        $committeeUrl = '/comites/en-marche-paris-8';

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeFollowLink($crawler), 'The guest should not see the "follow link"');
        $this->assertFalse($this->seeUnfollowLink($crawler), 'The guest should not see the "unfollow link"');
        $this->assertTrue($this->seeMembersCount($crawler, 1), 'The guest should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 1), 'The guest should see the hosts');
        $this->assertFalse($this->seeHostNav($crawler), 'The guest should not see the host navigation');
        $this->assertFalse($this->seeMessageForm($crawler));
        $this->seeMessageForContactHosts($crawler);
    }

    public function testAuthenticatedAdherentCanShowCommitteePage()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');

        $committeeUrl = '/comites/en-marche-paris-8';

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSeeHosts($crawler, [
            ['JP', 'Jacques Picard', 'Animateur'],
        ]);

        $this->assertFalse($this->seeRegisterLink($crawler, 0), 'The adherent should not see the "register link"');
        $this->assertFalse($this->seeLoginLink($crawler), 'The adherent should not see the "login link"');
        $this->assertTrue($this->seeFollowLink($crawler), 'The adherent should see the "follow link"');
        $this->assertFalse($this->seeUnfollowLink($crawler), 'The adherent should not see the "unfollow link"');
        $this->assertTrue($this->seeMembersCount($crawler, 1), 'The adherent should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 1), 'The adherent should see the hosts');
        $this->assertTrue($this->seeHostsContactLink($crawler, 1), 'The adherent should see the hosts contact link');
        $this->assertFalse($this->seeHostNav($crawler), 'The adherent should not see the host navigation');
        $this->assertFalse($this->seeMessageForm($crawler));
    }

    public function testAuthenticatedCommitteeFollowerCanShowCommitteePage()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites#committees');
        $crawler = $this->client->click($crawler->filter('a[title="En Marche Paris 8"]')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeRegisterLink($crawler, 0), 'The follower should not see the "register link"');
        $this->assertFalse($this->seeLoginLink($crawler), 'The adherent should not see the "login link"');
        $this->assertFalse($this->seeFollowLink($crawler), 'The follower should not see the "follow link"');
        $this->assertTrue($this->seeUnfollowLink($crawler), 'The follower should see the "unfollow link"');
        $this->assertTrue($this->seeMembersCount($crawler, 1), 'The follower should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 1), 'The follower should see the hosts');
        $this->assertTrue($this->seeHostsContactLink($crawler, 1), 'The follower should see the hosts contact link');
        $this->assertFalse($this->seeHostNav($crawler), 'The follower should not see the host navigation');
        $this->assertFalse($this->seeMessageForm($crawler));
    }

    private function seeLoginLink(Crawler $crawler): bool
    {
        return 1 === \count($crawler->filter('#committee-login-link'));
    }

    private function seeRegisterLink(Crawler $crawler, $nb = 1): bool
    {
        $this->assertCount($nb, $crawler->filter('.committee-follow--disabled'));

        return 1 === \count($crawler->filter('#committee-register-link'));
    }

    private function seeFollowLink(Crawler $crawler): bool
    {
        return 1 === \count($crawler->filter('.committee-follow'));
    }

    private function seeUnfollowLink(Crawler $crawler): bool
    {
        return 1 === \count($crawler->filter('.committee-unfollow'));
    }

    private function seeMembersCount(Crawler $crawler, int $membersCount): bool
    {
        return str_contains(
            $crawler->filter('.committee__infos')->text(),
            $membersCount.' adhérent'.($membersCount > 1 ? 's' : '')
        );
    }

    private function seeHosts(Crawler $crawler, int $hostsCount): bool
    {
        return $hostsCount === \count($crawler->filter('.committee__card .committee-host'));
    }

    private function assertSeeHosts(Crawler $crawler, array $hosts, bool $isConnected = true): void
    {
        $this->assertCount(\count($hosts), $nodes = $crawler->filter('.committee-host'));
        $contact = $isConnected ? '\s+(Contacter)' : '';

        foreach ($hosts as $position => $host) {
            [$initials, $name, $role] = $host;
            $this->assertMatchesRegularExpression(
                '/^'.preg_quote($initials).'\s+'.preg_quote($name).'\s+'.$role.$contact.'?$/',
                trim($nodes->eq($position)->text())
            );
        }
    }

    private function assertSeeDesignedAdherents(Crawler $crawler, array $adherents): void
    {
        $this->assertCount(\count($adherents), $nodes = $crawler->filter('.committee-designed-adherents'));

        foreach ($adherents as $position => $adherent) {
            [$initials, $name, $role] = $adherent;
            $this->assertMatchesRegularExpression(
                '/^'.preg_quote($initials).'\s+'.preg_quote($name).'\s+'.$role.'?$/',
                trim($nodes->eq($position)->text())
            );
        }
    }

    private function seeHostsContactLink(Crawler $crawler, int $hostsCount): bool
    {
        return $hostsCount === \count($crawler->filter('.committee__card .committee-host a'));
    }

    private function seeMessageForContactHosts(Crawler $crawler): void
    {
        $this->assertStringContainsString(
            'Connectez-vous pour pouvoir contacter les responsables de comité.',
            $crawler->filter('.committee__card > .text--summary')->text()
        );
    }

    private function seeHostNav(Crawler $crawler): bool
    {
        return 1 === \count($crawler->filter('#committee-host-nav'));
    }

    private function seeMessageForm(Crawler $crawler, array $errorMessages = []): bool
    {
        if ($errorMessages) {
            $errors = $crawler->filter('form[name="committee_feed_message"] .form__error');

            $this->assertCount(\count($errorMessages), $errors);

            foreach ($errorMessages as $i => $errorMessage) {
                $this->assertSame($errorMessage, trim($errors->eq($i)->text()));
            }
        } else {
            $this->assertCount(0, $crawler->filter('form[name="committee_feed_message"] .form__errors'));
        }

        return 1 === \count($crawler->filter('form[name="committee_feed_message"]'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
