<?php

namespace Tests\App\Controller\Renaissance;

use App\Entity\Adherent;
use App\Entity\Reporting\EmailSubscriptionHistory;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('adherent')]
class AdherentControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    public function testProfileActionIsAccessibleForAdherent(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/app');

        $this->assertClientIsRedirectedTo('/oauth/v2/auth?response_type=code&client_id=8128979a-cfdb-45d1-a386-f14f22bb19ae&redirect_uri=http://localhost:8081&scope=jemarche_app%20read:profile%20write:profile', $this->client);
    }

    public static function provideProfilePage(): \Generator
    {
        yield 'Mot de passe' => ['/parametres/mon-compte/changer-mot-de-passe'];
        yield 'Certification' => ['/espace-adherent/mon-compte/certification'];
    }

    public function testEditAdherentProfile(): void
    {
        $this->authenticateAsAdherent($this->client, 'renaissance-user-1@en-marche-dev.fr');

        $adherent = $this->getAdherentRepository()->findOneByEmail('renaissance-user-1@en-marche-dev.fr');
        $oldLatitude = $adherent->getLatitude();
        $oldLongitude = $adherent->getLongitude();
        $histories06Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe');
        $histories06Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe');
        $histories77Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe');
        $histories77Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe');

        $this->assertCount(0, $histories77Subscriptions);
        $this->assertCount(0, $histories77Unsubscriptions);
        $this->assertCount(0, $histories06Subscriptions);
        $this->assertCount(0, $histories06Unsubscriptions);

        $crawler = $this->client->request(Request::METHOD_GET, '/parametres/mon-compte');

        $inputPattern = 'input[name="adherent_profile[%s]"]';
        $optionPattern = 'select[name="adherent_profile[%s]"] option[selected="selected"]';

        self::assertSame('female', $crawler->filter(\sprintf($optionPattern, 'gender'))->attr('value'));
        self::assertSame('Laure', $crawler->filter(\sprintf($inputPattern, 'firstName'))->attr('value'));
        self::assertSame('Fenix', $crawler->filter(\sprintf($inputPattern, 'lastName'))->attr('value'));
        self::assertSame('2 avenue Jean Jaurès', $crawler->filter(\sprintf($inputPattern, 'postAddress][address'))->attr('value'));
        self::assertSame('77000', $crawler->filter(\sprintf($inputPattern, 'postAddress][postalCode'))->attr('value'));
        self::assertSame('France', $crawler->filter(\sprintf($optionPattern, 'postAddress][country'))->text());
        self::assertNull($crawler->filter(\sprintf($inputPattern, 'phone][number'))->attr('value'));
        self::assertSame('En activité', $crawler->filter(\sprintf($optionPattern, 'position'))->text());
        self::assertSame('1942-01-10', $crawler->filter(\sprintf($inputPattern, 'birthdate'))->attr('value'));
        self::assertAdherentHasZone($adherent, '77');

        // Submit the profile form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_profile' => [
                'emailAddress' => '',
                'gender' => 'male',
                'firstName' => '',
                'lastName' => '',
                'nationality' => '',
                'postAddress' => [
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
        self::assertSame(9, $errors->count());
        self::assertSame('Votre prénom doit comporter au moins 2 caractères.', $errors->eq(0)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(1)->text());
        self::assertSame('Votre nom doit comporter au moins 1 caractères.', $errors->eq(2)->text());
        self::assertSame('Cette valeur ne doit pas être vide.', $errors->eq(3)->text());
        self::assertSame('La nationalité est requise.', $errors->eq(4)->text());
        self::assertSame('L\'adresse email est requise.', $errors->eq(5)->text());
        self::assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.', $errors->eq(6)->text());
        self::assertSame('L\'adresse est obligatoire.', $errors->eq(7)->text());
        self::assertSame('Veuillez renseigner un code postal.', $errors->eq(8)->text());

        // Submit the profile form with too long input
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_profile' => [
                'emailAddress' => 'renaissance-user-1@en-marche-dev.fr',
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'nationality' => 'FR',
                'postAddress' => [
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
        self::assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.', $errors->eq(0)->text());
        self::assertSame('Cette valeur n\'est pas un code postal français valide.', $errors->eq(1)->text());
        self::assertSame('L\'adresse ne peut pas dépasser 150 caractères.', $errors->eq(2)->text());
        self::assertSame('Le code postal doit contenir moins de 15 caractères.', $errors->eq(3)->text());

        // Submit the profile form with valid data
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'adherent_profile' => [
                'gender' => 'female',
                'firstName' => 'Jean',
                'lastName' => 'Dupont',
                'postAddress' => [
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

        $histories06Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe');
        $histories06Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe');
        $histories77Subscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'subscribe');
        $histories77Unsubscriptions = $this->findEmailSubscriptionHistoryByAdherent($adherent, 'unsubscribe');

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

    /**
     * @return EmailSubscriptionHistory[]
     */
    public function findEmailSubscriptionHistoryByAdherent(Adherent $adherent, ?string $action = null): array
    {
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
}
