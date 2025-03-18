<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Mailer\Message\CommitteeCreationConfirmationMessage;
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

    #[DataProvider('provideCommitteesHostsAdherentsCredentials')]
    public function testAdherentsNotAllowedToCreateNewCommittees(string $emailAddress, string $warning): void
    {
        $this->authenticateAsAdherent($this->client, $emailAddress);
        $crawler = $this->client->request(Request::METHOD_GET, '/');
        $this->assertSame(0, $crawler->selectLink('Créer un comité')->count());

        // Try to cheat the system with a direct URL access.
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertStringContainsString($warning, $crawler->filter('.committee__warning')->first()->text());
    }

    public function testProvisionalSupervisorOfRefusedCommitteeCanCreateAnotherOne(): void
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');

        $crawler = $this->client->request(Request::METHOD_GET, '/');

        $this->assertSame(0, $crawler->selectLink('Créer un comité')->count());

        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-comite');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(0, $crawler->filter('.committee__warning'));
    }

    public static function provideCommitteesHostsAdherentsCredentials(): array
    {
        return [
            'Jacques Picard is already the owner of an existing committee' => [
                'jacques.picard@en-marche.fr',
                'Les parlementaires et les animateurs ne peuvent pas créer de comité.',
            ],
            'Gisèle Berthoux\'s profile is not certified' => [
                'gisele-berthoux@caramail.com',
                'Vous devez être certifié',
            ],
            'Deputy has an active parliamentary mandate' => [
                'deputy@en-marche-dev.fr',
                'Les parlementaires et les animateurs ne peuvent pas créer de comité.',
            ],
            'Lolodie Dutemps is minor' => [
                'lolodie.dutemps@hotnix.tld',
                'Vous devez être majeur pour créer un comité.',
            ],
        ];
    }

    #[DataProvider('provideRegularAdherentsCredentials')]
    public function testRegularAdherentCanCreateOneNewCommittee(string $emailAddress, string $phone): void
    {
        $this->client->followRedirects();

        $this->authenticateAsAdherent($this->client, $emailAddress);
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame($phone, $crawler->filter('#create_committee_phone_number')->attr('value'));

        // Submit the committee form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Envoyer ma demande')->form([
            'create_committee' => [
                'name' => 'F',
                'description' => 'F',
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(9, $crawler->filter('#create-committee-form .form__errors > li')->count());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $crawler->filter('#create_committee_address_errors > li.form__error')->eq(0)->text());
        $this->assertSame('Cette valeur n\'est pas un code postal français valide.', $crawler->filter('#create_committee_address_errors > li.form__error')->eq(1)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#create_committee_address_address_errors > li.form__error')->text());
        $this->assertSame('Le numéro de téléphone est obligatoire.', $crawler->filter('#create_committee_phone_errors > li.form__error')->text());
        $this->assertSame('Vous devez saisir au moins 2 caractères.', $crawler->filter('#create_committee_name_errors > li.form__error')->text());
        $this->assertSame('Votre texte de description est trop court. Il doit compter 5 caractères minimum.', $crawler->filter('#field-description > .form__errors > li')->text());
        $this->assertSame('Vous devez accepter les règles de confidentialité.', $crawler->filter('#field-confidentiality-terms > .form__errors > li')->text());
        $this->assertSame("Vous devez accepter d'être contacté(e) par la plateforme En Marche !", $crawler->filter('#field-contacting-terms > .form__errors > li')->text());

        // Submit the committee form with valid data to create committee
        $crawler = $this->client->submit($crawler->selectButton('Envoyer ma demande')->form([
            'create_committee[name]' => 'lyon est En Marche !',
            'create_committee[description]' => 'Comité français En Marche ! de la ville de Lyon',
            'create_committee[address][country]' => 'FR',
            'create_committee[address][address]' => '6 rue Neyret',
            'create_committee[address][postalCode]' => '69001',
            'create_committee[address][city]' => '69001-69381',
            'create_committee[address][cityName]' => '',
            'create_committee[phone][country]' => 'FR',
            'create_committee[phone][number]' => '0478457898',
            'create_committee[acceptConfidentialityTerms]' => true,
            'create_committee[acceptContactingTerms]' => true,
        ]));

        $this->assertInstanceOf(Committee::class, $committee = $this->committeeRepository->findMostRecentCommittee());
        $this->assertSame('Lyon est En Marche !', $committee->getName());
        $this->assertTrue($committee->isApproved());
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(CommitteeCreationConfirmationMessage::class, $emailAddress));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertEquals('http://'.$this->getParameter('app_host').'/parametres/mes-activites', $this->client->getRequest()->getUri());
        $this->seeFlashMessage($crawler, 'Votre comité a été créé avec succès. Il ne manque plus que la validation d\'un référent.');
    }

    public static function provideRegularAdherentsCredentials(): array
    {
        return [
            ['damien.schmidt@example.ch', '01 11 22 33 45'],
            ['adherent-male-a@en-marche-dev.fr', '06 99 00 88 00'],
        ];
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
