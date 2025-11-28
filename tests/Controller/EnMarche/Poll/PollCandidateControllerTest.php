<?php

declare(strict_types=1);

namespace Tests\App\Controller\EnMarche\Poll;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
class PollCandidateControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    /** @var NotificationRepository */
    private $notificationRepository;

    #[DataProvider('providePages')]
    public function testPollsPageIsForbiddenAsAnonymous(string $path): void
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    #[DataProvider('providePages')]
    public function testPollsPageIsForbiddenAsNotCandidateRegionalHeaded($path): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);

        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    #[DataProvider('providePages')]
    public function testAccessPollsPageAsCandidateRegionalHeaded($path): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(
            str_contains($path, 'publier') ? Response::HTTP_FOUND : Response::HTTP_OK,
            $this->client
        );
    }

    #[DataProvider('providePages')]
    public function testAccessPollsPageAsDelegatedCandidateRegionalHeaded($path): void
    {
        $this->authenticateAsAdherentWithChoosingSpace(
            'gisele-berthoux@caramail.com',
            'Espace candidat partagé (Île-de-France)'
        );

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(
            str_contains($path, 'publier') ? Response::HTTP_FOUND : Response::HTTP_OK,
            $this->client
        );
    }

    #[DataProvider('provideCandidates')]
    public function testSeePolls(string $email, string $spaceLinkName): void
    {
        $this->authenticateAsAdherentWithChoosingSpace($email, $spaceLinkName);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/question-du-jour');

        $this->assertCount(4, $polls = $crawler->filter('.datagrid table tbody tr'));
        $firstPollFields = $polls->eq(0)->filter('td');
        $this->assertSame('Tu dis "oui" ?', $firstPollFields->eq(1)->text());
        $this->assertSame('3', $firstPollFields->eq(4)->text());
        $this->assertSame('1', $firstPollFields->eq(5)->text());
        $this->assertStringContainsString('Île-de-France (11)', $firstPollFields->eq(6)->text());
        $this->assertSame('Jacques Picard', $firstPollFields->eq(7)->text());
        $this->assertSame('Publiée', $firstPollFields->eq(8)->text(null, true));
        $this->assertStringNotContainsString('Non publiée', $firstPollFields->eq(8)->text());
        $this->assertCount(1, $firstPollFields->eq(9)->filter('a:contains("Editer")'));
        $this->assertCount(1, $firstPollFields->eq(9)->filter('a:contains("Dépublier")'));

        $secondPollFields = $polls->eq(1)->filter('td');

        $this->assertSame('Tu dis "non" ?', $secondPollFields->eq(1)->text());
        $this->assertSame('1', $secondPollFields->eq(4)->text());
        $this->assertSame('2', $secondPollFields->eq(5)->text());
        $this->assertStringContainsString('Île-de-France (11)', $secondPollFields->eq(6)->text());
        $this->assertSame('Jacques Picard', $secondPollFields->eq(7)->text());
        $this->assertSame('Non publiée', $secondPollFields->eq(8)->text(null, true));
        $this->assertCount(1, $secondPollFields->eq(9)->filter('a:contains("Editer")'));
        $this->assertCount(1, $secondPollFields->eq(9)->filter('a:contains("Publier")'));
    }

    #[DataProvider('provideCandidates')]
    public function testCreatePollFailed(string $email, string $spaceLinkName): void
    {
        $this->authenticateAsAdherentWithChoosingSpace($email, $spaceLinkName);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/question-du-jour/creer');

        $data = [];

        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form(), $data);

        $this->assertStatusCode(200, $this->client);
        $this->assertSame(3, $crawler->filter('.form__errors > li')->count());
        $this->assertSame('La question est requise.',
            $crawler->filter('#poll_question_errors > li')->eq(0)->text());
        $this->assertSame('La question doit contenir au moins 2 caractères.',
            $crawler->filter('#poll_question_errors > li')->eq(1)->text());
        $this->assertSame('La date de fin est requise.',
            $crawler->filter('#poll_finishAt_errors > li')->text());
    }

    #[DataProvider('provideCandidates')]
    public function testCreatePollSuccessful(string $email, string $spaceLinkName): void
    {
        $notifications = $this->notificationRepository->findAll();
        self::assertEmpty($notifications);

        $this->authenticateAsAdherentWithChoosingSpace($email, $spaceLinkName);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/question-du-jour/creer');

        $data = [];
        $data['poll']['finishAt'] = '2023-06-15 00:00';
        $data['poll']['question'] = 'Ma question "Test"';
        $data['poll']['published'] = 1;

        $this->client->submit($crawler->selectButton('Enregistrer')->form(), $data);
        $this->assertClientIsRedirectedTo('/espace-candidat/question-du-jour', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertStatusCode(200, $this->client);
        $this->assertSame(0, $crawler->filter('.form__errors > li')->count());
        $this->assertSame('La question du jour a bien été enregistrée.', $crawler->filter('.flash--info')->text(null, true));
    }

    #[DataProvider('provideCandidates')]
    public function testPublishPoll(string $email, string $spaceLinkName): void
    {
        $this->authenticateAsAdherentWithChoosingSpace($email, $spaceLinkName);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/question-du-jour');
        $this->assertStatusCode(200, $this->client);

        $secondPollFields = $crawler->filter('.datagrid table tbody tr')->eq(1)->filter('td');
        $this->assertSame('Non publiée', $secondPollFields->eq(8)->text(null, true));
        $this->assertCount(1, $crawler->filter('.datagrid table tbody tr td span.status__1:contains("Publiée")'));
        $this->assertCount(3, $crawler->filter('.datagrid table tbody tr td span.status__2:contains("Non publiée")'));

        $this->client->request(Request::METHOD_GET, '/espace-candidat/question-du-jour/c45f204d-cf49-4bf7-9a51-bd1fc89a7260/publier');
        $this->assertClientIsRedirectedTo('/espace-candidat/question-du-jour', $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(200, $this->client);

        $this->assertSame('La question "Tu dis "non" ?" a bien été publiée', $crawler->filter('.flash--info')->text(null, true));
        $this->assertCount(0, $crawler->filter('.flash--error'));

        $secondPollFields = $crawler->filter('.datagrid table tbody tr')->eq(1)->filter('td');

        $this->assertSame('Tu dis "non" ?', $secondPollFields->eq(1)->text(null, true));
        $this->assertSame('Publiée', $secondPollFields->eq(8)->text(null, true));
        $this->assertCount(1, $crawler->filter('.datagrid table tbody tr td span.status__1:contains("Publiée")'));
        $this->assertCount(3, $crawler->filter('.datagrid table tbody tr td span.status__2:contains("Non publiée")'));

        $this->client->request(Request::METHOD_GET, '/espace-candidat/question-du-jour/c45f204d-cf49-4bf7-9a51-bd1fc89a7260/publier');
        $this->assertClientIsRedirectedTo('/espace-candidat/question-du-jour', $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(200, $this->client);

        $this->assertSame('La question "Tu dis "non" ?" est déjà publiée', $crawler->filter('.flash--error')->text(null, true));
        $this->assertCount(0, $crawler->filter('.flash--info'));

        $secondPollFields = $crawler->filter('.datagrid table tbody tr')->eq(1)->filter('td');

        $this->assertSame('Publiée', $secondPollFields->eq(8)->text(null, true));
        $this->assertCount(1, $crawler->filter('.datagrid table tbody tr td span.status__1:contains("Publiée")'));
        $this->assertCount(3, $crawler->filter('.datagrid table tbody tr td span.status__2:contains("Non publiée")'));
    }

    #[DataProvider('provideCandidates')]
    public function testUnpublishPoll(string $email, string $spaceLinkName): void
    {
        $this->authenticateAsAdherentWithChoosingSpace($email, $spaceLinkName);

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat/question-du-jour');
        $this->assertStatusCode(200, $this->client);

        $firstPollFields = $crawler->filter('.datagrid table tbody tr')->eq(0)->filter('td');
        $this->assertSame('Publiée', $firstPollFields->eq(8)->text(null, true));
        $this->assertCount(1, $crawler->filter('.datagrid table tbody tr td span.status__1:contains("Publiée")'));
        $this->assertCount(3, $crawler->filter('.datagrid table tbody tr td span.status__2:contains("Non publiée")'));

        $this->client->request(Request::METHOD_GET, '/espace-candidat/question-du-jour/655d7534-9592-4aed-83e6-cad8fbb3668f/depublier');
        $this->assertClientIsRedirectedTo('/espace-candidat/question-du-jour', $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(200, $this->client);

        $this->assertSame('La question "Tu dis "oui" ?" a bien été dépubliée', $crawler->filter('.flash--info')->text(null, true));
        $this->assertCount(0, $crawler->filter('.flash--error'));

        $firstPollFields = $crawler->filter('.datagrid table tbody tr')->eq(0)->filter('td');

        $this->assertSame('Tu dis "oui" ?', $firstPollFields->eq(1)->text(null, true));
        $this->assertSame('Non publiée', $firstPollFields->eq(8)->text(null, true));
        $this->assertCount(0, $crawler->filter('.datagrid table tbody tr td span.status__1:contains("Publiée")'));
        $this->assertCount(4, $crawler->filter('.datagrid table tbody tr td span.status__2:contains("Non publiée")'));

        $this->client->request(Request::METHOD_GET, '/espace-candidat/question-du-jour/655d7534-9592-4aed-83e6-cad8fbb3668f/depublier');
        $this->assertClientIsRedirectedTo('/espace-candidat/question-du-jour', $this->client);
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(200, $this->client);

        $this->assertSame('La question "Tu dis "oui" ?" est déjà dépubliée', $crawler->filter('.flash--error')->text(null, true));
        $this->assertCount(0, $crawler->filter('.flash--info'));

        $firstPollFields = $crawler->filter('.datagrid table tbody tr')->eq(0)->filter('td');

        $this->assertSame('Non publiée', $firstPollFields->eq(8)->text(null, true));
        $this->assertCount(0, $crawler->filter('.datagrid table tbody tr td span.status__1:contains("Publiée")'));
        $this->assertCount(4, $crawler->filter('.datagrid table tbody tr td span.status__2:contains("Non publiée")'));
    }

    public static function providePages(): iterable
    {
        return [
            ['/espace-candidat/question-du-jour'],
            ['/espace-candidat/question-du-jour/creer'],
            ['/espace-candidat/question-du-jour/f91b332e-efef-4bf6-89ad-b9675e42a3f5/editer'],
            ['/espace-candidat/question-du-jour/f91b332e-efef-4bf6-89ad-b9675e42a3f5/publier'],
            ['/espace-candidat/question-du-jour/655d7534-9592-4aed-83e6-cad8fbb3668f/depublier'],
        ];
    }

    public static function provideCandidates(): iterable
    {
        yield ['jacques.picard@en-marche.fr', 'Espace candidat'];  // candidate region headed
        yield ['gisele-berthoux@caramail.com', 'Espace candidat partagé (Île-de-France)']; // has a delegated access
    }

    protected function setUp(): void
    {
        $this->markTestSkipped();

        parent::setUp();

        $this->disableRepublicanSilence();
        $this->notificationRepository = $this->getRepository(Notification::class);
    }

    protected function tearDown(): void
    {
        $this->notificationRepository = null;

        parent::tearDown();
    }
}
