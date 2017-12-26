<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\Mailer\Message\LegislativeCampaignContactMessage;
use AppBundle\Repository\EmailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 * @group legislatives
 */
class LegislativesControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @var EmailRepository
     */
    private $emailRepository;

    public function testAnonymousUserIsForbiddenToSendContactMessage()
    {
        $this->client->request(Request::METHOD_GET, '/espace-candidat-legislatives/contact');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $crawler = $this->client->followRedirect();

        $this->assertSame(0, $crawler->filter('#legislatives_campaign_contact_form')->count());
        $this->assertSame(1, $crawler->filter('#_adherent_email')->count());
        $this->assertSame(1, $crawler->filter('#_adherent_password')->count());
    }

    public function testRegularAdherentIsForbiddenToSendContactMessage()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat-legislatives/contact');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
        $this->assertSame(0, $crawler->filter('#legislatives_campaign_contact_form')->count());
    }

    /**
     * @dataProvider provideLegislativeCandidatesContacts
     */
    public function testLegislativeCandidateIsAllowedToSendContactMessage(string $expectedRecipient, string $selectedRecipient)
    {
        $this->authenticateAsAdherent($this->client, 'kiroule.p@blabla.tld', 'politique2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-candidat-legislatives/contact');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('#legislatives_campaign_contact_form')->count());

        $crawler = $this->client->click($crawler->selectButton("J'envoie ma demande")->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('#legislatives_campaign_contact_form')->count());
        $this->assertSame(0, $crawler->filter('#notice-flashes')->count());
        $this->assertSame(7, $crawler->filter('#legislatives_campaign_contact_form .form__error')->count());
        $this->assertCount(0, $this->emailRepository->findMessages(LegislativeCampaignContactMessage::class));

        $this->client->click($crawler->selectButton("J'envoie ma demande")->form([
            'legislative_campaign_contact_message' => [
                'emailAddress' => 'marc1337@gmail.tld',
                'firstName' => 'Marc 🍇',
                'lastName' => '🍎 Dupont',
                'departmentNumber' => '92',
                'electoralDistrictNumber' => '3',
                'role' => 'Responsable communication',
                'recipient' => $selectedRecipient,
                'subject' => '🍔 Avez-vous pensez aux réseaux sociaux ? 🍔',
                'message' => 'Puis-je avoir accès aux comptes Twitter 🐦 et Facebook 📆 svp ?',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());

        $emails = $this->emailRepository->findRecipientMessages(LegislativeCampaignContactMessage::class, $expectedRecipient);
        $this->assertCount(1, $emails);

        $payload = $emails[0]->getRequestPayload();

        $this->assertSame('Marc Dupont', $payload['FromName']);
        $this->assertSame('Élections Législatives - Nouvelle demande de contact', $payload['Subject']);
        $this->assertSame('143247', $payload['MJ-TemplateID']);
        $this->assertSame(
            [
                'email' => 'marc1337@gmail.tld',
                'first_name' => 'Marc',
                'last_name' => 'Dupont',
                'department_number' => '92',
                'electoral_district_number' => '3',
                'role' => 'Responsable communication',
                'subject' => 'Avez-vous pensez aux réseaux sociaux ?',
                'message' => 'Puis-je avoir accès aux comptes Twitter  et Facebook  svp ?',
            ],
            $payload['Vars']
        );

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('#legislatives_campaign_contact_form')->count());
        $this->assertContains("Votre demande d'information a été envoyée avec succès.", $crawler->filter('#notice-flashes')->text());
        $this->assertSame(0, $crawler->filter('#legislatives_campaign_contact_form .form__error')->count());
        $this->assertCount(1, $this->emailRepository->findMessages(LegislativeCampaignContactMessage::class));
    }

    public static function provideLegislativeCandidatesContacts(): array
    {
        return [
            ['comptes.legislatives@en-marche.fr', 'financial'],
            ['campagne.legislatives@en-marche.fr', 'standard'],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadHomeBlockData::class,
        ]);

        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->emailRepository = null;

        parent::tearDown();
    }
}
