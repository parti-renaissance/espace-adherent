<?php

namespace Tests\App\Controller\Renaissance;

use App\Adherent\Command\RemoveAdherentAndRelatedDataCommand;
use App\Adherent\Handler\RemoveAdherentAndRelatedDataCommandHandler;
use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\SubscriptionType;
use App\Entity\Unregistration;
use App\Mailer\Message\Renaissance\RenaissanceAdherentTerminateMembershipMessage;
use App\Repository\EmailRepository;
use App\Repository\UnregistrationRepository;
use App\Subscription\SubscriptionTypeEnum;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('adherent')]
class AdherentControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    /* @var EmailRepository */
    private $emailRepository;

    #[DataProvider('provideProfilePage')]
    public function testProfileActionIsSecured(string $profilePage): void
    {
        $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    #[DataProvider('provideProfilePage')]
    public function testProfileActionIsAccessibleForAdherent(string $profilePage): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Laure Fenix', trim($crawler->filter('h6')->text()));
        $this->assertStringContainsString('Inscrite depuis le 25 janvier 2017', $crawler->filter('#adherent-since')->text());
    }

    #[DataProvider('provideProfilePage')]
    public function testProfileActionIsNotAccessibleForEMAdherent(string $profilePage): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/', $this->client);
    }

    public static function provideProfilePage(): \Generator
    {
        yield 'Mes informations personnelles' => ['/parametres/mon-compte'];
        yield 'Mot de passe' => ['/parametres/mon-compte/changer-mot-de-passe'];
        yield 'Certification' => ['/espace-adherent/mon-compte/certification'];
    }

    public function testEditAdherentProfile(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $adherent = $this->getAdherentRepository()->findOneByEmail('renaissance-user-1@en-marche-dev.fr');
        $oldLatitude = $adherent->getLatitude();
        $oldLongitude = $adherent->getLongitude();
        $histories06Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '06');
        $histories06Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '06');
        $histories77Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '77');
        $histories77Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '77');

        $this->assertCount(0, $histories77Subscriptions);
        $this->assertCount(0, $histories77Unsubscriptions);
        $this->assertCount(0, $histories06Subscriptions);
        $this->assertCount(0, $histories06Unsubscriptions);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $inputPattern = 'input[name="adherent_profile[%s]"]';
        $optionPattern = 'select[name="adherent_profile[%s]"] option[selected="selected"]';

        self::assertSame('female', $crawler->filter(sprintf($optionPattern, 'gender'))->attr('value'));
        self::assertSame('Laure', $crawler->filter(sprintf($inputPattern, 'firstName'))->attr('value'));
        self::assertSame('Fenix', $crawler->filter(sprintf($inputPattern, 'lastName'))->attr('value'));
        self::assertSame('2 avenue Jean Jaurès', $crawler->filter(sprintf($inputPattern, 'address][address'))->attr('value'));
        self::assertSame('77000', $crawler->filter(sprintf($inputPattern, 'address][postalCode'))->attr('value'));
        self::assertSame('France', $crawler->filter(sprintf($optionPattern, 'address][country'))->text());
        self::assertSame(null, $crawler->filter(sprintf($inputPattern, 'phone][number'))->attr('value'));
        self::assertSame('En activité', $crawler->filter(sprintf($optionPattern, 'position'))->text());
        self::assertSame('1942-01-10', $crawler->filter(sprintf($inputPattern, 'birthdate'))->attr('value'));
        self::assertCount(1, $adherent->getReferentTags());
        self::assertAdherentHasZone($adherent, '77');

        // Submit the profile form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_profile' => [
                'emailAddress' => '',
                'gender' => 'male',
                'firstName' => '',
                'lastName' => '',
                'nationality' => '',
                'address' => [
                    'address' => '',
                    'country' => 'FR',
                    'postalCode' => '',
                    'cityName' => '',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '',
                ],
                'position' => 'student',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $errors = $crawler->filter('.re-form-error');
        self::assertSame(7, $errors->count());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        self::assertSame('La nationalité est requise.', $errors->eq(2)->text());
        self::assertSame('L\'adresse email est requise.', $errors->eq(3)->text());
        self::assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.', $errors->eq(4)->text());
        self::assertSame('L\'adresse est obligatoire.', $errors->eq(5)->text());
        self::assertSame('Veuillez renseigner un code postal.', $errors->eq(6)->text());

        // Submit the profile form with too long input
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_profile' => [
                'emailAddress' => 'renaissance-user-1@en-marche-dev.fr',
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'nationality' => 'FR',
                'address' => [
                    'address' => 'Une adresse de 150 caractères, ça peut arriver.Une adresse de 150 caractères, ça peut arriver.Une adresse de 150 caractères, ça peut arriver.Oui oui oui.',
                    'country' => 'FR',
                    'postalCode' => '0600000000000000',
                    'cityName' => 'Nice, France',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '01 01 02 03 04',
                ],
                'position' => 'student',
                'birthdate' => '1985-10-27',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $errors = $crawler->filter('.re-form-error');

        self::assertSame(4, $errors->count());
        self::assertSame('Cette valeur n\'est pas un code postal français valide.', $errors->eq(0)->text());
        self::assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.', $errors->eq(1)->text());
        self::assertSame('L\'adresse ne peut pas dépasser 150 caractères.', $errors->eq(2)->text());
        self::assertSame('Le code postal doit contenir moins de 15 caractères.', $errors->eq(3)->text());

        // Submit the profile form with valid data
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_profile' => [
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'address' => [
                    'address' => '9 rue du Lycée',
                    'country' => 'FR',
                    'postalCode' => '06000',
                    'cityName' => 'Nice',
                ],
                'phone' => [
                    'country' => 'FR',
                    'number' => '01 01 02 03 04',
                ],
                'position' => 'student',
                'birthdate' => '1985-10-27',
            ],
        ]));

        $this->assertClientIsRedirectedTo('/parametres/mon-compte', $this->client);

        $crawler = $this->client->followRedirect();

        $this->seeFlashMessage($crawler, 'Vos informations ont été mises à jour avec succès.');

        // We need to reload the manager reference to get the updated data
        /** @var Adherent $adherent */
        $adherent = $this->client->getContainer()->get('doctrine')->getManager()->getRepository(Adherent::class)->findOneByEmail('renaissance-user-1@en-marche-dev.fr');

        self::assertSame('female', $adherent->getGender());
        self::assertSame('Jean Dupont', $adherent->getFullName());
        self::assertSame('9 rue du Lycée', $adherent->getAddress());
        self::assertSame('06000', $adherent->getPostalCode());
        self::assertSame('Nice', $adherent->getCityName());
        self::assertSame('101020304', $adherent->getPhone()->getNationalNumber());
        self::assertSame('student', $adherent->getPosition());
        $this->assertNotNull($newLatitude = $adherent->getLatitude());
        $this->assertNotNull($newLongitude = $adherent->getLongitude());
        $this->assertNotSame($oldLatitude, $newLatitude);
        $this->assertNotSame($oldLongitude, $newLongitude);
        self::assertCount(2, $adherent->getReferentTags());
        self::assertAdherentHasReferentTag($adherent, '06');
        self::assertAdherentHasReferentTag($adherent, 'CIRCO_06001');

        $histories06Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '06');
        $histories06Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '06');
        $histories77Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe', '77');
        $histories77Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe', '77');

        $this->assertCount(0, $histories77Subscriptions);
        $this->assertCount(0, $histories77Unsubscriptions);
        $this->assertCount(0, $histories06Subscriptions);
        $this->assertCount(0, $histories06Unsubscriptions);
    }

    public function testCertifiedAdherentCanNotEditFields(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-2@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $disabledFields = $crawler->filter('form[name="adherent_profile"] input[disabled="disabled"], form[name="adherent_profile"] select[disabled="disabled"]');
        self::assertCount(4, $disabledFields);
        self::assertEquals('adherent_profile[firstName]', $disabledFields->eq(0)->attr('name'));
        self::assertEquals('adherent_profile[lastName]', $disabledFields->eq(1)->attr('name'));
        self::assertEquals('adherent_profile[birthdate]', $disabledFields->eq(2)->attr('name'));
        self::assertEquals('adherent_profile[gender]', $disabledFields->eq(3)->attr('name'));
    }

    public function testAdherentChangePassword(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/changer-mot-de-passe');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

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

        $errors = $crawler->filter('.re-form-error');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        self::assertSame(2, $errors->count());
        self::assertSame('Le mot de passe est invalide.', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());

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
        string $action = null,
        string $referentTagCode = null
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

        if ($referentTagCode) {
            $qb
                ->leftJoin('history.referentTags', 'tag')
                ->andWhere('tag.code = :code')
                ->setParameter('code', $referentTagCode)
            ;
        }

        return $qb->getQuery()->getResult();
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
        yield 'PAD' => ['president-ad@renaissance-dev.fr'];
        yield 'RCL' => ['adherent-male-55@en-marche-dev.fr'];
    }

    #[DataProvider('provideAdherentCredentials')]
    public function testAdherentTerminatesMembership(string $userEmail, string $uuid): void
    {
        /** @var Adherent $adherent */
        $adherentBeforeUnregistration = $this->getAdherentRepository()->findOneByEmail($userEmail);
        $referentTagsBeforeUnregistration = $adherentBeforeUnregistration->getReferentTags()->toArray(); // It triggers the real SQL query instead of lazy-load

        $this->authenticateAsAdherent($this->client, $userEmail);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte/desadherer');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->submit($crawler->selectButton('Je confirme la suppression de mon adhésion')->form());

        $this->assertEquals('http://'.$this->getParameter('app_renaissance_host').'/parametres/mon-compte/desadherer', $this->client->getRequest()->getUri());

        $errors = $crawler->filter('.re-form-error');

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertSame(0, $errors->count());
        $this->assertStringContainsString('Votre adhésion et votre compte Renaissance ont bien été supprimés, vos données personnelles ont été effacées de notre base.', $this->client->getResponse()->getContent());

        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(RenaissanceAdherentTerminateMembershipMessage::class, $userEmail));

        $this->client->getContainer()->get('test.'.RemoveAdherentAndRelatedDataCommandHandler::class)(
            new RemoveAdherentAndRelatedDataCommand(Uuid::fromString($uuid))
        );

        /** @var Adherent $adherent */
        $adherent = $this->getAdherentRepository()->findOneByEmail($userEmail);

        $this->assertNull($adherent);

        /** @var Unregistration $unregistration */
        $unregistration = $this->get(UnregistrationRepository::class)->findOneByUuid($uuid);
        $mailHistorySubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherentBeforeUnregistration, 'subscribe');
        $mailHistoryUnsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherentBeforeUnregistration, 'unsubscribe');

        $this->assertSame(\count($mailHistorySubscriptions), \count($mailHistoryUnsubscriptions));
        $this->assertEmpty($unregistration->getReasons());
        $this->assertNull($unregistration->getComment());
        $this->assertSame($adherentBeforeUnregistration->getRegisteredAt()->format('Y-m-d H:i:s'), $unregistration->getRegisteredAt()->format('Y-m-d H:i:s'));
        $this->assertSame((new \DateTime())->format('Y-m-d'), $unregistration->getUnregisteredAt()->format('Y-m-d'));
        $this->assertSame($adherentBeforeUnregistration->getUuid()->toString(), $unregistration->getUuid()->toString());
        $this->assertSame($adherentBeforeUnregistration->getPostalCode(), $unregistration->getPostalCode());
        $this->assertEquals($referentTagsBeforeUnregistration, $unregistration->getReferentTags()->toArray());
    }

    public function testBlockedCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertStringContainsString('Demande de certification bloquée', $crawler->filter('#certification')->text());

        $this->client->request('GET', '/espace-adherent/mon-compte/certification/demande');
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/certification', $this->client);

        $this->client->followRedirect();
        $this->assertResponseStatusCode(200, $this->client->getResponse());
    }

    public static function provideAdherentCredentials(): array
    {
        return [
            'adherent 1' => ['renaissance-user-1@en-marche-dev.fr', LoadAdherentData::RENAISSANCE_USER_1_UUID],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;

        parent::tearDown();
    }

    private function getSubscriptionTypesFormValues(array $codes): array
    {
        return array_map(static function (SubscriptionType $type) use ($codes) {
            return \in_array($type->getCode(), $codes, true) ? $type->getId() : false;
        }, $this->getSubscriptionTypeRepository()->findByCodes(SubscriptionTypeEnum::ADHERENT_TYPES));
    }
}
