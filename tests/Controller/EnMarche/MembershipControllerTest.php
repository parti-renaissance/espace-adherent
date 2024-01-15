<?php

namespace Tests\App\Controller\EnMarche;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Repository\AdherentActivationTokenRepository;
use App\Repository\AdherentRepository;
use App\Repository\EmailRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('membership')]
class MembershipControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var AdherentActivationTokenRepository
     */
    private $activationTokenRepository;

    /**
     * @var EmailRepository
     */
    private $emailRepository;

    #[DataProvider('provideEmailAddress')]
    public function testCannotCreateMembershipAccountWithSomeoneElseEmailAddress(string $emailAddress): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/inscription-utilisateur');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $data = static::createFormData();
        $data['user_registration']['emailAddress']['first'] = $emailAddress;
        $data['user_registration']['emailAddress']['second'] = $emailAddress;
        $data['user_registration']['nationality'] = 'FR';
        $crawler = $this->client->submit($crawler->selectButton('Créer mon compte')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Cette adresse email existe déjà.', $crawler->filter('.form__error')->text());
    }

    /**
     * These data come from the LoadAdherentData fixtures file.
     *
     * @see LoadAdherentData
     */
    public static function provideEmailAddress(): array
    {
        return [
            ['michelle.dufour@example.ch'],
            ['carl999@example.fr'],
        ];
    }

    public function testAdherentSubscriptionTypesArePersistedCorrectly(): void
    {
        $this->client->request(Request::METHOD_GET, '/adhesion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit(
            $this->client->getCrawler()->selectButton('Je rejoins La République En Marche')->form(),
            [
                'g-recaptcha-response' => 'fake',
                'adherent_registration' => [
                    'firstName' => 'Test',
                    'lastName' => 'A',
                    'nationality' => 'FR',
                    'emailAddress' => [
                        'first' => 'test@test.com',
                        'second' => 'test@test.com',
                    ],
                    'password' => '12345678',
                    'address' => [
                        'address' => '1 rue des alouettes',
                        'postalCode' => '94320',
                        'cityName' => 'Thiais',
                        'city' => '94320-94073',
                        'country' => 'FR',
                    ],
                    'birthdate' => [
                        'day' => 1,
                        'month' => 1,
                        'year' => 1989,
                    ],
                    'gender' => 'male',
                    'conditions' => true,
                    'allowEmailNotifications' => true,
                    'allowMobileNotifications' => true,
                ],
            ]
        );

        $this->assertClientIsRedirectedTo('/inscription/centre-interets', $this->client);
        $adherent = $this->adherentRepository->findOneByEmail('test@test.com');

        self::assertCount(8, $adherent->getSubscriptionTypes());
    }

    public function testAdherentSubscriptionTypesArePersistedCorrectlyWhenAdhesionFromUser(): void
    {
        $adherent = $this->adherentRepository->findOneByEmail('simple-user@example.ch');

        self::assertCount(0, $adherent->getSubscriptionTypes());

        $this->authenticateAsAdherent($this->client, 'simple-user@example.ch');
        $this->client->request(Request::METHOD_GET, '/adhesion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit(
            $this->client->getCrawler()->selectButton('Je rejoins La République En Marche')->form(),
            [
                'become_adherent' => [
                    'nationality' => 'FR',
                    'address' => [
                        'address' => '32 Zeppelinstrasse',
                        'cityName' => 'Zürich',
                        'postalCode' => '8057',
                        'country' => 'CH',
                    ],
                    'phone' => [
                        'number' => '06 12 34 56 78',
                    ],
                    'birthdate' => [
                        'day' => 1,
                        'month' => 1,
                        'year' => 1989,
                    ],
                    'gender' => 'male',
                    'conditions' => true,
                    'allowEmailNotifications' => true,
                    'allowMobileNotifications' => false,
                ],
            ]
        );

        $this->assertClientIsRedirectedTo('/espace-adherent/accueil', $this->client);

        $crawler = $this->client->followRedirect();

        $this->isSuccessful($this->client->getResponse());
        self::assertSame('Votre compte adhérent est maintenant actif. Pour poursuivre votre action militante, n\'hésitez pas à vous rendre sur l\'application mobile Je m\'engage.', $crawler->filter('.flash__inner')->text());

        $adherent = $this->adherentRepository->findOneByEmail('simple-user@example.ch');

        self::assertCount(7, $adherent->getSubscriptionTypes());
    }

    public function testCreateAdherentWithCustomGender(): void
    {
        $this->client->request(Request::METHOD_GET, '/adhesion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit(
            $this->client->getCrawler()->selectButton('Je rejoins La République En Marche')->form(),
            [
                'g-recaptcha-response' => 'fake',
                'adherent_registration' => [
                    'firstName' => 'Test',
                    'lastName' => 'Adhesion',
                    'emailAddress' => [
                        'first' => 'custom.gender@example.fr',
                        'second' => 'custom.gender@example.fr',
                    ],
                    'password' => '12345678',
                    'address' => [
                        'address' => '92 bld victor hugo',
                        'postalCode' => '92110',
                        'cityName' => 'Clichy',
                        'city' => '92110-92024',
                        'country' => 'FR',
                    ],
                    'birthdate' => [
                        'day' => 1,
                        'month' => 1,
                        'year' => 1989,
                    ],
                    'gender' => 'other',
                    'customGender' => 'my custom gender',
                    'nationality' => 'FR',
                    'conditions' => true,
                    'allowEmailNotifications' => true,
                    'allowMobileNotifications' => true,
                ],
            ]
        );

        $this->assertClientIsRedirectedTo('/inscription/centre-interets', $this->client);

        $adherent = $this->adherentRepository->findOneByEmail('custom.gender@example.fr');

        self::assertSame('other', $adherent->getGender());
        self::assertSame('my custom gender', $adherent->getCustomGender());
        self::assertTrue($adherent->hasPapUserRole());
    }

    private static function createFormData(): array
    {
        return [
            'g-recaptcha-response' => 'dummy',
            'user_registration' => [
                'firstName' => 'jean-pauL',
                'lastName' => 'duPont',
                'nationality' => 'FR',
                'emailAddress' => [
                    'first' => 'jean-paul@dupont.tld',
                    'second' => 'jean-paul@dupont.tld',
                ],
                'password' => LoadAdherentData::DEFAULT_PASSWORD,
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '92110',
                ],
                'allowEmailNotifications' => true,
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->activationTokenRepository = $this->getActivationTokenRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;
        $this->activationTokenRepository = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }
}
