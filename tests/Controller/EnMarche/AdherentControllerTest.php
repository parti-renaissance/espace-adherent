<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Repository\CommitteeRepository;
use App\Repository\Email\EmailLogRepository;
use Cake\Chronos\Chronos;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('adherent')]
class AdherentControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var EmailLogRepository */
    private $emailRepository;
    private $subscriptionTypeRepository;

    public function testAuthenticatedAdherentCanSeeHisUpcomingAndPastEvents(): void
    {
        Chronos::setTestNow('2018-05-18');

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $crawler = $this->client->click($crawler->selectLink('Mes événements')->link());

        // first page
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(4, $crawler->filter('#upcoming-events article')->count());
        $titles = $crawler->filter('#upcoming-events h2');
        $this->assertSame('Meeting de New York City', trim($titles->first()->text()));
        $this->assertSame('Réunion de réflexion parisienne', trim($titles->eq(1)->text()));
        $this->assertSame('Réunion de réflexion dammarienne', trim($titles->eq(2)->text()));
        $this->assertSame('Réunion de réflexion parisienne annulé', trim($titles->eq(3)->text()));

        $this->assertSame(5, $crawler->filter('#past-events article')->count());
        $titles = $crawler->filter('#past-events h2');
        $this->assertSame('Meeting de Singapour', trim($titles->first()->text()));
        $this->assertSame('Grand débat parisien', trim($titles->eq(1)->text()));
        $this->assertSame('Événement à Paris 1', trim($titles->eq(2)->text()));
        $this->assertSame('Événement à Paris 2', trim($titles->eq(3)->text()));
        $this->assertSame('Marche Parisienne', trim($titles->eq(4)->text()));

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mes-activites?page=2&type=past_events#events');
        self::assertEquals('http://test.enmarche.code/parametres/mes-activites?page=2&type=past_events#events', $crawler->getUri());
        $titles = $crawler->filter('#past-events h2');
        $this->assertSame(2, $crawler->filter('#past-events article')->count());
        $this->assertSame('Grand Meeting de Paris', trim($titles->first()->text()));
        $this->assertSame('Grand Meeting de Marseille', trim($titles->last()->text()));

        Chronos::setTestNow();
    }

    #[DataProvider('provideProfilePage')]
    public function testProfileActionIsAccessibleForAdherent(string $profilePage): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Carl Mirabeau', $crawler->filter('.adherent-profile__id .name')->text());
        $this->assertStringContainsString('Adhérent depuis le16 novembre 2016 à 20:45', $crawler->filter('.adherent-profile__id .adhesion-date')->text());
    }

    #[DataProvider('provideProfilePage')]
    public function testProfileActionIsNotAccessibleForREAdherent(string $profilePage): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString(
            'Rendez vous sur test.renaissance.code pour modifier votre profil!',
            $crawler->filter('.adherent-profile__section')->text()
        );
    }

    public static function provideProfilePage(): \Generator
    {
        yield 'Mes informations personnelles' => ['/parametres/mon-compte'];
        yield 'Mes centres d\'intérêt' => ['/espace-adherent/mon-compte/centres-d-interet'];
        yield 'Mot de passe' => ['/parametres/mon-compte/changer-mot-de-passe'];
        yield 'Certification' => ['/espace-adherent/mon-compte/certification'];
    }

    public function testProfileActionIsAccessibleForInactiveAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'thomas.leclerc@example.ch');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Thomas Leclerc', $crawler->filter('.adherent-profile__id .name')->text());
        $this->assertStringContainsString('Non adhérent.', $crawler->filter('.adherent-profile__id .adhesion-date')->text());
    }

    public function testCertifiedAdherentCanNotEditFields(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $disabledFields = $crawler->filter('form[name="adherent_profile"] input[disabled="disabled"], form[name="adherent_profile"] select[disabled="disabled"]');
        self::assertCount(5, $disabledFields);
        self::assertEquals('adherent_profile[firstName]', $disabledFields->eq(0)->attr('name'));
        self::assertEquals('adherent_profile[lastName]', $disabledFields->eq(1)->attr('name'));
        self::assertEquals('adherent_profile[gender]', $disabledFields->eq(2)->attr('name'));
        self::assertEquals('adherent_profile[customGender]', $disabledFields->eq(3)->attr('name'));
        self::assertEquals('adherent_profile[birthdate]', $disabledFields->eq(4)->attr('name'));
    }

    public function testEditAdherentInterests(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mon-compte/centres-d-interet');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $checkBoxPattern = 'form[name="app_adherent_pin_interests"] .checkb-cols input[type="checkbox"][name="app_adherent_pin_interests[interests][]"]';

        $this->assertCount(23, $checkboxes = $crawler->filter($checkBoxPattern));

        $interests = $this->getParameter('adherent_interests');
        $interestsValues = array_keys($interests);
        $interestsLabels = array_values($interests);

        foreach ($checkboxes as $i => $checkbox) {
            self::assertSame($interestsValues[$i], $checkbox->getAttribute('value'));
            self::assertSame($interestsLabels[$i], $crawler->filter('label[for="app_adherent_pin_interests_interests_'.$i.'"]')->eq(0)->text());
        }

        $interests = $this->getParameter('adherent_interests');
        $interestsValues = array_keys($interests);

        $chosenInterests = [
            4 => $interestsValues[4],
            8 => $interestsValues[8],
        ];

        $this->client->submit($crawler->selectButton('Enregistrer')->form(), [
            'app_adherent_pin_interests' => [
                'interests' => $chosenInterests,
            ],
        ]);

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/centres-d-interet', $this->client);

        /* @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findOneByEmail('carl999@example.fr');

        self::assertSame(array_values($chosenInterests), $adherent->getInterests());

        $crawler = $this->client->followRedirect();

        $this->assertCount(23, $checkboxes = $crawler->filter($checkBoxPattern));

        foreach ($checkboxes as $i => $checkbox) {
            if (isset($chosenInterests[$i])) {
                self::assertSame('checked', $checkbox->getAttribute('checked'));
            } else {
                $this->assertEmpty($crawler->filter('label[for="app_adherent_pin_interests_interests_'.$i.'"]')->eq(0)->attr('checked'));
            }
        }
    }

    public function testAdherentChangePassword(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/changer-mot-de-passe');

        $this->assertCount(1, $crawler->filter('input[name="adherent_change_password[old_password]"]'));
        $this->assertCount(1, $crawler->filter('input[name="adherent_change_password[password][first]"]'));
        $this->assertCount(1, $crawler->filter('input[name="adherent_change_password[password][second]"]'));

        // Submit the profile form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('adherent_change_password[submit]')->form(), [
            'adherent_change_password' => [
                'old_password' => '',
                'password' => [
                    'first' => '',
                    'second' => '',
                ],
            ],
        ]);

        $errors = $crawler->filter('.em-form--error');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame(3, $errors->count());
        self::assertSame('Le mot de passe est invalide.', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        self::assertSame('Votre mot de passe doit comporter au moins 8 caractères.', $errors->eq(2)->text());

        // Submit the profile form with valid data
        $this->client->submit($crawler->selectButton('adherent_change_password[submit]')->form(), [
            'adherent_change_password' => [
                'old_password' => 'secret!12345',
                'password' => [
                    'first' => 'heaneaheah',
                    'second' => 'heaneaheah',
                ],
            ],
        ]);

        $this->assertClientIsRedirectedTo('/parametres/mon-compte/changer-mot-de-passe', $this->client);
    }

    /**
     * @return EmailSubscriptionHistory[]
     */
    public function findEmailSubscriptionHistoryByAdherent(
        Adherent $adherent,
        ?string $action = null,
    ): array {
        $qb = $this
            ->getEmailSubscriptionHistoryRepository()
            ->createQueryBuilder('history')
            ->where('history.adherentUuid = :adherentUuid')
            ->setParameter('adherentUuid', $adherent->getUuid())
            ->orderBy('history.date', 'DESC')
        ;

        if ($action) {
            $qb
                ->andWhere('history.action = :action')
                ->setParameter('action', $action)
            ;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return EmailSubscriptionHistory[]
     */
    public function findAllEmailSubscriptionHistoryByAdherentAndType(
        Adherent $adherent,
        string $subscriptionType,
    ): array {
        return $this
            ->getEmailSubscriptionHistoryRepository()
            ->createQueryBuilder('history')
            ->join('history.subscriptionType', 'subscriptionType')
            ->where('history.adherentUuid = :adherentUuid')
            ->andWhere('subscriptionType.code = :type')
            ->orderBy('history.date ', 'DESC')
            ->setParameter('adherentUuid', $adherent->getUuid())
            ->setParameter('type', $subscriptionType)
            ->getQuery()
            ->getResult()
        ;
    }

    public function testDocumentsActionIsAccessibleAsAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString('Documents', $this->client->getResponse()->getContent());
    }

    public function testContactActionWithInvalidUuid(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/wrong-uuid');

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);

        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID, [
            'id' => 'wrong-uuid',
            'from' => 'event',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);

        $this->client->request(Request::METHOD_GET, '/espace-adherent/contacter/'.LoadAdherentData::ADHERENT_1_UUID, [
            'id' => 'wrong-uuid',
            'from' => 'committee',
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    #[DataProvider('dataProviderCannotTerminateMembership')]
    public function testCannotTerminateMembership(string $email): void
    {
        $this->authenticateAsAdherent($this->client, $email);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertStringNotContainsString(
            'Si vous souhaitez désadhérer et supprimer votre compte En Marche, cliquez-ici.',
            $crawler->text()
        );

        $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/desadherer');

        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    public static function dataProviderCannotTerminateMembership(): \Generator
    {
        yield 'CommitteeCandidate' => ['adherent-female-a@en-marche-dev.fr'];
    }

    public static function provideAdherentCredentials(): array
    {
        return [
            'adherent 1' => ['michel.vasseur@example.ch', LoadAdherentData::ADHERENT_13_UUID, 'en-marche-suisse', 3],
            'adherent 2' => ['cedric.lebon@en-marche-dev.fr', LoadAdherentData::ADHERENT_19_UUID, 'en-marche-comite-de-evry', 6],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->committeeRepository = $this->getCommitteeRepository();
        $this->emailRepository = $this->getEmailRepository();
        $this->subscriptionTypeRepository = $this->getSubscriptionTypeRepository();
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;
        $this->committeeRepository = null;
        $this->subscriptionTypeRepository = null;

        parent::tearDown();
    }
}
