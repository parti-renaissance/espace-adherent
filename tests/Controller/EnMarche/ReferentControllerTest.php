<?php

namespace Tests\App\Controller\EnMarche;

use App\Address\GeoCoder;
use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use App\DataFixtures\ORM\LoadElectedRepresentativeData;
use App\Entity\InstitutionalEvent;
use App\Entity\ReferentManagedUsersMessage;
use App\Mailer\Message\EventRegistrationConfirmationMessage;
use App\Mailer\Message\InstitutionalEventInvitationMessage;
use App\Repository\ReferentManagedUsersMessageRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

/**
 * @group functional
 * @group referent
 */
class ReferentControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    /**
     * @var ReferentManagedUsersMessageRepository
     */
    private $referentMessageRepository;

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('/connexion', $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAdherentNotReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsAccessibleAsCoReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');
        $this->client->request(Request::METHOD_GET, $path);

        if ('/espace-referent/utilisateurs' === $path) {
            $this->assertStatusCode(Response::HTTP_OK, $this->client);
        } else {
            $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
        }
    }

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsAccessibleAsReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    /**
     * @dataProvider providePages
     */
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

        $this->client->submit($this->client->getCrawler()->selectButton('Créer cet événement')->form(), $data);
        $this->assertSame(5, $this->client->getCrawler()->filter('.form__errors')->count());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#committee-event-name-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#committee-event-description-field > .form__errors > li')->text());
        $this->assertSame('L\'adresse est obligatoire.',
            $this->client->getCrawler()->filter('#committee-event-address-address-field > .form__errors > li')->text());
        $this->assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
            $this->client->getCrawler()->filter('#committee-event-address > .form__errors > li')->text());
        $this->assertSame('Veuillez sélectionner un pays.',
            $this->client->getCrawler()->filter('#committee-event-address-country-field > .form__errors > li')->text());
    }

    public function testCreateEventSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements/creer');

        $data = [];
        $data['committee_event']['name'] = 'premier événement';
        $data['committee_event']['category'] = $this->getEventCategoryIdForName('Événement innovant');
        $data['committee_event']['beginAt']['date']['day'] = 14;
        $data['committee_event']['beginAt']['date']['month'] = 6;
        $data['committee_event']['beginAt']['date']['year'] = date('Y');
        $data['committee_event']['beginAt']['time']['hour'] = preg_replace('/0/', '', date('H'), 1);
        $data['committee_event']['beginAt']['time']['minute'] = 0;
        $data['committee_event']['finishAt']['date']['day'] = 15;
        $data['committee_event']['finishAt']['date']['month'] = 6;
        $data['committee_event']['finishAt']['date']['year'] = date('Y');
        $data['committee_event']['finishAt']['time']['hour'] = 23;
        $data['committee_event']['finishAt']['time']['minute'] = 0;
        $data['committee_event']['address']['address'] = 'Pilgerweg 58';
        $data['committee_event']['address']['cityName'] = 'Kilchberg';
        $data['committee_event']['address']['postalCode'] = '8802';
        $data['committee_event']['address']['country'] = 'CH';
        $data['committee_event']['description'] = 'Premier événement en Suisse';
        $data['committee_event']['capacity'] = 100;
        $data['committee_event']['timeZone'] = 'Europe/Zurich';

        $this->client->submit($this->client->getCrawler()->selectButton('Créer cet événement')->form(), $data);

        $this->assertSame('L\'événement « Premier événement » a bien été créé.', $this->client->getCrawler()->filter('.box-success h2')->text());

        $this->assertSame(
            'Votre événement est en ligne mais pas encore diffusé. Partagez-le par message en cliquant ci-dessous.',
            trim($this->client->getCrawler()->filter('.box-success .alert--tips')->text())
        );

        $this->assertCountMails(1, EventRegistrationConfirmationMessage::class, 'referent@en-marche-dev.fr');
    }

    public function testCreateInstitutionalEventSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements-institutionnels/creer');

        $data = [];
        $data['institutional_event']['name'] = 'Un événement institutionnel en Suisse';
        $data['institutional_event']['category'] = $this->getInstitutionalEventCategoryIdByName('Comité politique');
        $data['institutional_event']['beginAt']['date']['day'] = 14;
        $data['institutional_event']['beginAt']['date']['month'] = 6;
        $data['institutional_event']['beginAt']['date']['year'] = date('Y');
        $data['institutional_event']['beginAt']['time']['hour'] = preg_replace('/0/', '', date('H'), 1);
        $data['institutional_event']['beginAt']['time']['minute'] = 0;
        $data['institutional_event']['finishAt']['date']['day'] = 15;
        $data['institutional_event']['finishAt']['date']['month'] = 6;
        $data['institutional_event']['finishAt']['date']['year'] = date('Y');
        $data['institutional_event']['finishAt']['time']['hour'] = 23;
        $data['institutional_event']['finishAt']['time']['minute'] = 0;
        $data['institutional_event']['address']['address'] = 'Pilgerweg 58';
        $data['institutional_event']['address']['cityName'] = 'Kilchberg';
        $data['institutional_event']['address']['postalCode'] = '8802';
        $data['institutional_event']['address']['country'] = 'CH';
        $data['institutional_event']['description'] = 'Premier événement institutionel en Suisse';
        $data['institutional_event']['timeZone'] = 'Europe/Zurich';
        $data['institutional_event']['invitations'] = 'jean@exemple.fr;michel@exemple.fr;marcel@exemple.fr';

        $this->client->submit(
            $this->client->getCrawler()->selectButton('Créer cette réunion privée')->form(), $data
        );

        /** @var InstitutionalEvent $event */
        $event = $this->getInstitutionalEventRepository()->findOneBy(
            ['name' => 'Un événement institutionnel en Suisse']
        );

        $this->assertInstanceOf(InstitutionalEvent::class, $event);
        $this->assertClientIsRedirectedTo('/espace-referent/evenements-institutionnels', $this->client);

        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains(
            'Le nouvel événement institutionnel a bien été créé.',
            $this->client->getCrawler()->filter('div.flash--info')->html()
        );

        $this->assertCountMails(1, InstitutionalEventInvitationMessage::class, 'referent@en-marche-dev.fr');
    }

    public function testCreateInstitutionalEventFailed()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements-institutionnels/creer');

        // Submit the form with empty values
        $this->client->submit($this->client->getCrawler()
            ->selectButton('Créer cette réunion privée')->form(), [])
        ;

        $this->assertSame(6, $this->client->getCrawler()->filter('.form__errors')->count());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#institutional_event-name-field > .form__errors > li')->text()
        );

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#institutional_event-description-field > .form__errors > li')->text()
        );

        $this->assertSame("L'adresse est obligatoire.",
            $this->client->getCrawler()->filter(
                '#institutional_event-address-address-field > .form__errors > li')->text()
        );

        $this->assertSame('Veuillez sélectionner un pays.',
            $this->client->getCrawler()->filter('#institutional_event-address-country-field > .form__errors > li')->text()
        );

        $this->assertSame('Vous devez saisir au moins une adresse email.',
            $this->client->getCrawler()->filter('#institutional_event-invitations-field > .form__errors > li')->text()
        );

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#institutional_event-description-field > .form__errors > li')->text()
        );
    }

    /**
     * @dataProvider provideUsers
     */
    public function testSearchUserToSendMail($user)
    {
        $this->authenticateAsAdherent($this->client, $user);

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        self::assertSame(4, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $data = [
            'f' => [
                'city' => 77,
            ],
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(1, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        self::assertSame(4, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $data = [
            'f' => [
                'city' => 'Melun',
            ],
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(1, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $data = [
            'f' => [
                'city' => 'FR',
            ],
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(2, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $data = [
            'f' => [
                'city' => 13,
            ],
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(0, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $data = [
            'f' => [
                'city' => 59,
            ],
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(0, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

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

        // Managed Area
        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'f' => [
                'referentTags' => 100,
            ],
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Appliquer')->form(), $data);
        self::assertSame(1, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());
    }

    public function testFilterAdherents()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');

        $this->assertCount(4, $this->client->getCrawler()->filter('tbody tr.referent__item'));

        // filter hosts
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeAdherentsNoCommittee]'] = false;
        $form['f[includeAdherentsInCommittee]'] = false;
        $form['f[includeRoles]'] = ['CommitteeHosts'];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(2, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertContains('Gisele', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());
        $this->assertContains('Berthoux', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());

        // filter supervisors
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeAdherentsNoCommittee]'] = false;
        $form['f[includeAdherentsInCommittee]'] = false;
        $form['f[includeRoles]'] = ['CommitteeSupervisors'];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(0, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertContains('Brioul Francis', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());

        // filter newsletter subscriptions
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeAdherentsNoCommittee]'] = false;
        $form['f[includeAdherentsInCommittee]'] = false;
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(5, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(4, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertContains('77000', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertContains('8802', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());

        // exclude
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeAdherentsNoCommittee]'] = true;
        $form['f[includeAdherentsInCommittee]'] = true;
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = ['CitizenProjectHosts', 'CommitteeSupervisors', 'CommitteeHosts'];

        $this->client->submit($form);

        $this->assertCount(3, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertContains('8802', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertContains('8057', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());

        // filter adherents in no committee
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeAdherentsNoCommittee]'] = true;
        $form['f[includeAdherentsInCommittee]'] = false;
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(3, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(3, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--adherent'));
        $this->assertContains('Francis', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertContains('Brioul', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertContains('Gisele', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('Berthoux', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('Michelle', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(2)->text());

        // filter adherents in committees
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeAdherentsNoCommittee]'] = false;
        $form['f[includeAdherentsInCommittee]'] = true;
        $form['f[includeRoles]'] = [];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(2, $this->client->getCrawler()->filter('.status.status__1'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--adherent'));
        $this->assertContains('Michel', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());

        // filter adherents in CP
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[includeAdherentsNoCommittee]'] = false;
        $form['f[includeAdherentsInCommittee]'] = false;
        $form['f[includeRoles]'] = ['CitizenProjectHosts'];
        $form['f[excludeRoles]'] = [];

        $this->client->submit($form);

        $this->assertCount(0, $this->client->getCrawler()->filter('.status.status__1'));
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

        $crawlerOptions = $crawler->filter('#referent_filter_referentTags option');

        self::assertCount(7, $crawlerOptions);
        self::assertSame('Département 13', $crawlerOptions->getNode(0)->nodeValue);
        self::assertSame('Département 59', $crawlerOptions->getNode(1)->nodeValue);
        self::assertSame('Département 76', $crawlerOptions->getNode(2)->nodeValue);
        self::assertSame('Département 77', $crawlerOptions->getNode(3)->nodeValue);
        self::assertSame('Département 92', $crawlerOptions->getNode(4)->nodeValue);
        self::assertSame('Espagne', $crawlerOptions->getNode(5)->nodeValue);
        self::assertSame('Suisse', $crawlerOptions->getNode(6)->nodeValue);

        $crawler = $this->client->submit(
            $crawler->selectButton('Filtrer')->form([
                'referent_filter' => [
                    'referentTags' => 14,
                ],
            ])
        );
        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        self::assertCount(0, $crawler->filter('.form .form__errors'));
    }

    public function testListElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus');

        $this->assertCount(4, $crawler->filter('tbody tr.referent__item'));
        $this->assertCount(1, $crawler->filter('.status.status__1'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(e) municipal(e) (NC)Clichy (92110)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Maire', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('PS (2016)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Non', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('Conseiller(e) municipal(e) (DIV)Rouen (76000)', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains(' G.s (2018) PS (2014 à 2018)', preg_replace('/\s+/', ' ', $crawler->filter('tbody tr.referent__item')->eq(1)->filter('td')->eq(4)->text()));
        $this->assertContains('Non', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('DUFOUR Michelle', $crawler->filter('tbody tr.referent__item')->eq(2)->text());
        $this->assertContains('Oui', $crawler->filter('tbody tr.referent__item')->eq(2)->text());
        $this->assertContains('LOBELL', $crawler->filter('tbody tr.referent__item')->eq(3)->text());
    }

    public function testFilterElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus');

        $this->assertCount(4, $crawler->filter('tbody tr.referent__item'));

        // filter by lastname
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = 'BOU';

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter by firstname
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = 'BOU';
        $form['f[firstName]'] = 'Delphine';

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by labels
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = '';
        $form['f[firstName]'] = '';
        $form['f[labels]'] = ['PS', 'LaREM'];

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('DUFOUR Michelle', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter by politicalFunctions
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[labels]'] = ['PS', 'LaREM'];
        $form['f[politicalFunctions]'] = ['mayor'];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by mandates
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[labels]'] = [];
        $form['f[politicalFunctions]'] = [];
        $form['f[mandates]'] = ['membre_EPCI', 'depute'];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('LOBELL André', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by gender
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[mandates]'] = [];
        $form['f[gender]'] = 'female';

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('DUFOUR Michelle', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter not adherents
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[gender]'] = '';
        $form['f[isAdherent]'] = 0;

        $crawler = $this->client->submit($form);

        $this->assertCount(3, $crawler->filter('tbody tr.referent__item'));

        // filter by cities
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[isAdherent]'] = 0;
        $form['f[cities]'] = [6];

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('LOBELL André', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter by userListDefinitions
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[isAdherent]'] = 0;
        $form['f[cities]'] = [6];
        $form['f[userListDefinitions]'] = [3];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
    }

    public function testListElectedRepresentativesForParis()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus');

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('PARIS Arrondissement', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('PARISS Circonscription', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
    }

    public function testShowElectedRepresentative()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus/'.LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_1_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Michelle DUFOUR', $crawler->filter('.elected-representative-identity')->eq(0)->text());
        $this->assertContains('François Hollande', $crawler->filter('.elected-representative-sponsorships tbody tr')->eq(0)->text());
        $this->assertContains('Emmanuel Macron', $crawler->filter('.elected-representative-sponsorships tbody tr')->eq(1)->text());
        $this->assertCount(1, $crawler->filter('.elected-representative-mandates tbody tr'));
        $this->assertContains('Conseiller(e) municipal(e)', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertContains('REM', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertContains('Soutien officiel', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertContains('23 juillet 2019', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertContains(' Autre membre (2019) Président(e) d\'EPCI (2015)', preg_replace('/\s+/', ' ', $crawler->filter('.elected-representative-mandates tbody tr')->eq(0)->filter('td')->eq(5)->text()));
        $this->assertCount(1, $crawler->filter('.elected-representative-labels tbody tr'));
        $this->assertNotContains('Aucune', $crawler->filter('.elected-representative-labels tbody tr')->text());
        $this->assertCount(0, $crawler->filter('.sponsorships tbody tr'));
        $this->assertCount(1, $crawler->filter('.elected-representative-candidacies tbody tr'));
        $this->assertContains('Aucune', $crawler->filter('.elected-representative-candidacies tbody tr')->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus/'.LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_2_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Delphine BOUILLOUX', $crawler->filter('.elected-representative-identity')->eq(0)->text());
        $this->assertCount(5, $crawler->filter('.elected-representative-social-networks a'));

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus/'.LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_3_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Daniel BOULON', $crawler->filter('.elected-representative-identity')->eq(0)->text());
        $this->assertCount(1, $crawler->filter('.elected-representative-mandates tbody tr'));
        $this->assertCount(2, $crawler->filter('.elected-representative-labels tbody tr'));
        $this->assertCount(0, $crawler->filter('.sponsorships tbody tr'));
        $this->assertCount(1, $crawler->filter('.elected-representative-candidacies tbody tr'));
        $this->assertContains('Membre d\'EPCI', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
        $this->assertContains('DIV', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
        $this->assertContains('Pas soutenu', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
        $this->assertContains('11 janvier 2017', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
    }

    public function providePages()
    {
        return [
            ['/espace-referent/utilisateurs'],
            ['/espace-referent/elus'],
            ['/espace-referent/evenements'],
            ['/espace-referent/comites'],
            ['/espace-referent/projets-citoyens'],
            ['/espace-referent/evenements/creer'],
        ];
    }

    public function provideUsers(): iterable
    {
        yield ['referent@en-marche-dev.fr'];
        yield ['benjyd@aol.com'];
    }

    /**
     * @return string Date in the format "Jeudi 14 juin 2018, 9h00"
     */
    private function formatEventDate(\DateTime $date, $timeZone = GeoCoder::DEFAULT_TIME_ZONE): string
    {
        $formatter = new \IntlDateFormatter(
            'fr_FR',
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::NONE,
            $timeZone,
            \IntlDateFormatter::GREGORIAN,
            'EEEE d LLLL Y, H');

        return ucfirst(strtolower($formatter->format($date).'h00'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->referentMessageRepository = $this->manager->getRepository(ReferentManagedUsersMessage::class);

        $this->disableRepublicanSilence();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->referentMessageRepository = null;

        parent::tearDown();
    }
}
