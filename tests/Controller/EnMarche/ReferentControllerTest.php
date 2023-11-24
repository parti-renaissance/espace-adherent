<?php

namespace Tests\App\Controller\EnMarche;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\DataFixtures\ORM\LoadDelegatedAccessData;
use App\Entity\Geo\Zone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

#[Group('functional')]
#[Group('referent')]
class ReferentControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    #[DataProvider('providePages')]
    public function testReferentBackendIsForbiddenAsAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    #[DataProvider('providePages')]
    public function testReferentBackendIsForbiddenAsAdherentNotReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    #[DataProvider('providePages')]
    public function testReferentBackendIsAccessibleAsReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    #[DataProvider('providePages')]
    public function testChangeOfPageAccessInformationToReferentSpace($path)
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');
        $adherent = $this->getAdherentRepository()->findOneByEmail('referent@en-marche-dev.fr');
        $accessInformation = $this->getReferentSpaceAccessInformationRepository()->findByAdherent($adherent);

        $this->assertNull($accessInformation);

        $this->client->request(Request::METHOD_GET, $path);
        $this->manager->clear();
        $accessInformation = $this->getReferentSpaceAccessInformationRepository()->findByAdherent($adherent);

        $this->assertNotNull($accessInformation);
        $this->assertNotNull($accessInformation->getLastDate());
        $this->assertNotNull($accessInformation->getPreviousDate());
    }

    public function testCreateEventFailed()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements/creer');

        $data = [];

        $this->client->submit($this->client->getCrawler()->selectButton('Enregistrer')->form(), $data);
        $this->assertSame(6, $this->client->getCrawler()->filter('.form__errors')->count());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#event-name-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être nulle.',
            $this->client->getCrawler()->filter('#event-category-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#event-description-field > .form__errors > li')->text());
        $this->assertSame('L\'adresse est obligatoire.',
            $this->client->getCrawler()->filter('#event-address-address-field > .form__errors > li')->text());
    }

    public function testCreateEventSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements/creer');

        $data = [];
        $data['event_command']['name'] = 'premier événement';
        $data['event_command']['category'] = $this->getEventCategoryIdForName('Événement innovant');
        $data['event_command']['beginAt'] = '2023-06-14 16:15';
        $data['event_command']['finishAt'] = '2023-06-15 23:00';
        $data['event_command']['address']['address'] = 'Pilgerweg 58';
        $data['event_command']['address']['cityName'] = 'Kilchberg';
        $data['event_command']['address']['postalCode'] = '8802';
        $data['event_command']['address']['country'] = 'CH';
        $data['event_command']['description'] = 'Premier événement en Suisse';
        $data['event_command']['capacity'] = 100;
        $data['event_command']['timeZone'] = 'Europe/Zurich';
        $data['event_command']['private'] = true;

        $this->client->submit($this->client->getCrawler()->selectButton('Enregistrer')->form(), $data);

        $this->assertSame('L\'événement « Premier événement » a bien été créé.', $this->client->getCrawler()->filter('.box-success h2')->text());

        $this->assertSame(
            'Votre événement est en ligne mais pas encore diffusé. Partagez-le par message en cliquant ci-dessous.',
            trim($this->client->getCrawler()->filter('.box-success .alert--tips')->text())
        );
    }

    #[DataProvider('provideUsers')]
    public function testSearchUserToSendMail($user)
    {
        $this->authenticateAsAdherent($this->client, $user);

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        self::assertSame(4, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();

        /* @var ChoiceFormField $zonesField */
        $zonesField = $form->get('f[zones]');
        $zonesField->disableValidation()->setValue([
            $this->getRepository(Zone::class)->findOneBy([
                'type' => Zone::DEPARTMENT,
                'code' => '77', // Seine-et-Marne
            ])->getId(),
        ]);

        $this->client->submit($form);
        self::assertSame(1, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        self::assertSame(4, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();

        /* @var ChoiceFormField $zonesField */
        $zonesField = $form->get('f[zones]');
        $zonesField->disableValidation()->setValue([
            $this->getRepository(Zone::class)->findOneBy([
                'type' => Zone::CITY,
                'code' => '77288', // Melun
            ])->getId(),
        ]);

        $this->client->submit($form);
        self::assertSame(1, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        // Gender
        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'f' => [
                'gender' => 'male',
            ],
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(3, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'f' => [
                'gender' => 'female',
            ],
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(1, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        // Firstname
        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'f' => [
                'gender' => 'male',
                'firstName' => 'Mich',
            ],
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(2, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        // Lastname
        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'f' => [
                'lastName' => 'ou',
            ],
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(3, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());
    }

    public function testFilterAdherents()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');

        $this->assertCount(4, $this->client->getCrawler()->filter('tbody tr.referent__item'));

        // filter hosts
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = ['CommitteeHosts'];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(2, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertStringContainsString('Gisele', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());
        $this->assertStringContainsString('Berthoux', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());

        // filter supervisors
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = ['CommitteeSupervisors'];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(0, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertStringContainsString('Brioul Francis', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());

        // filter newsletter subscriptions
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(5, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(4, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('77000', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertStringContainsString('8802', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertStringContainsString('92110', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(2)->text());

        // exclude
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = ['CommitteeSupervisors', 'CommitteeHosts'];

        $this->client->submit($form);

        $this->assertCount(3, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('8802', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertStringContainsString('8057', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());

        // filter adherents in no committee
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[isCommitteeMember]'] = 0;
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(1, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertStringContainsString('Michelle', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());

        // filter adherents in committees
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[isCommitteeMember]'] = 1;
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(4, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(3, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--adherent'));
        $this->assertStringContainsString('Francis', $this->client->getCrawler()->filter('tbody tr.referent__item--host')->first()->text());
        $this->assertStringContainsString('Gisele', $this->client->getCrawler()->filter('tbody tr.referent__item--host')->eq(1)->text());
        $this->assertStringContainsString('Michel', $this->client->getCrawler()->filter('tbody tr.referent__item--adherent')->text());

        // filter certified adherents
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeRoles]'] = [];
        $form['f[isCertified]'] = 1;

        $this->client->submit($form);

        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr .adherent-name > img'));
        $this->assertStringContainsString('Berthoux', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
    }

    public function testReferentCanCreateAdherentMessageSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-referent/messagerie/creer');
        $this->client->submit($crawler->selectButton('Suivant')->form(['adherent_message' => [
            'label' => 'test',
            'subject' => 'subject',
            'content' => 'message content',
        ]]));

        $this->assertTrue($this->client->getResponse()->isRedirection());

        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        $crawler = $this->client->followRedirect();

        $crawlerOptions = $crawler->filter('#referent_filter_zones option');

        self::assertCount(7, $crawlerOptions);
        self::assertSame('Espagne (ES)', $crawlerOptions->getNode(0)->nodeValue);
        self::assertSame('Suisse (CH)', $crawlerOptions->getNode(1)->nodeValue);
        self::assertSame('Seine-et-Marne (77)', $crawlerOptions->getNode(2)->nodeValue);
        self::assertSame('Hauts-de-Seine (92)', $crawlerOptions->getNode(3)->nodeValue);
        self::assertSame('Seine-Maritime (76)', $crawlerOptions->getNode(4)->nodeValue);
        self::assertSame('Nord (59)', $crawlerOptions->getNode(5)->nodeValue);
        self::assertSame('Bouches-du-Rhône (13)', $crawlerOptions->getNode(6)->nodeValue);

        $crawler = $this->client->submit(
            $crawler->selectButton('Filtrer')->form([
                'referent_filter' => [
                    'zones' => 72,
                ],
            ])
        );
        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        self::assertCount(0, $crawler->filter('.form .form__errors'));
    }

    public function testAdherentWithDelegatedAccessSeeAdherents()
    {
        $this->authenticateAsAdherent($this->client, 'senateur@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, sprintf('/espace-partage/%s', LoadDelegatedAccessData::ACCESS_UUID_7));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-referent/utilisateurs', $this->client);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(4, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(0, $this->client->getCrawler()->filter('tbody tr .adherent-name > img'));
    }

    public static function providePages(): array
    {
        return [
            ['/espace-referent/utilisateurs'],
            ['/espace-referent/elus'],
            ['/espace-referent/evenements'],
            ['/espace-referent/comites'],
            ['/espace-referent/evenements/creer'],
        ];
    }

    public static function provideUsers(): iterable
    {
        yield ['referent@en-marche-dev.fr'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->disableRepublicanSilence();
    }
}
