<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Committee;
use AppBundle\Mailjet\Message\CommitteeCreationConfirmationMessage;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\MailjetEmailRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdherentControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var CommitteeRepository */
    private $committeeRepository;

    /* @var MailjetEmailRepository */
    private $emailRepository;

    /**
     * @dataProvider provideProfilePage
     */
    public function testProfileActionIsSecured($profilePage)
    {
        $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);
    }

    /**
     * @dataProvider provideProfilePage
     */
    public function testProfileActionIsAccessibleForAdherent($profilePage, $title)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, $profilePage);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $current = $crawler->filter('.adherent_profile .adherent-profile-menu ul li a.active'));
        $this->assertSame($profilePage, $current->attr('href'));
        $this->assertSame('Carl Mirabeau', $crawler->filter('.adherent_profile > h2')->text());
        $this->assertSame('carl999@example.fr', $crawler->filter('.adherent_profile > p')->text());
        $this->assertSame($title, $crawler->filter('.adherent_profile h3')->text());
    }

    public function provideProfilePage()
    {
        yield ['/espace-adherent/mon-profil', 'Informations personnelles'];

        yield ['/espace-adherent/mon-profil/centres-d-interet', 'Centres d\'intérêt'];

        yield ['/espace-adherent/mon-profil/changer-mot-de-passe', 'Mot de passe'];

        yield ['/espace-adherent/mon-profil/preferences-des-email', 'Préférences des e-mails'];
    }

    /**
     * @dataProvider provideCommitteesHostsAdherentsCredentials
     */
    public function testCommitteesAdherentsHostsAreNotAllowedToCreateNewCommittees(string $emaiLAddress, string $password)
    {
        $crawler = $this->authenticateAsAdherent($this->client, $emaiLAddress, $password);
        $this->assertSame(0, $crawler->selectLink('Créer un comité')->count());

        // Try to cheat the system with a direct URL access.
        $this->client->request(Request::METHOD_GET, '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function provideCommitteesHostsAdherentsCredentials()
    {
        return [
            'Jacques Picard is already the owner of an existing committee' => [
                'jacques.picard@en-marche.fr',
                'changeme1337',
            ],
            'Gisèle Berthoux was promoted the host privilege of an existing committee' => [
                'gisele-berthoux@caramail.com',
                'ILoveYouManu',
            ],
            'Benjamin Duroc created a committee that is still under approval' => [
                'benjyd@aol.com',
                'HipHipHip',
            ],
        ];
    }

    /**
     * @dataProvider provideRegularAdherentsCredentials
     */
    public function testRegularAdherentCanCreateOneNewCommittee(string $emaiLAddress, string $password)
    {
        $crawler = $this->authenticateAsAdherent($this->client, $emaiLAddress, $password);
        $crawler = $this->client->click($crawler->selectLink('Créer un comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Submit the committee form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Créer mon comité')->form([
            'committee' => [
                'name' => 'F',
                'description' => 'F',
                'country' => 'FR',
                'postalCode' => '10102',
                'city' => '10102-45029',
                'facebookPageUrl' => 'yo',
                'twitterNickname' => '@!!',
                'googlePlusPageUrl' => 'yo',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(9, $crawler->filter('#create-committee-form .form__errors > li')->count());
        $this->assertSame("Cette valeur n'est pas un identifiant valide de ville française.", $crawler->filter('#create-committee-form > .form__errors > li')->text());
        $this->assertSame('Cette chaîne est trop courte. Elle doit avoir au minimum 2 caractères.', $crawler->filter('#field-name > .form__errors > li')->text());
        $this->assertSame('Cette chaîne est trop courte. Elle doit avoir au minimum 5 caractères.', $crawler->filter('#field-description > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas un code postal français valide.", $crawler->filter('#field-postal-code-city > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-facebook-page-url > .form__errors > li')->text());
        $this->assertSame('Un identifiant Twitter ne peut contenir que des lettres, des chiffres et des underscores.', $crawler->filter('#field-twitter-nickname > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-googleplus-page-url > .form__errors > li')->text());
        $this->assertSame('Vous devez accepter les règles de confidentialité.', $crawler->filter('#field-confidentiality-terms > .form__errors > li')->text());
        $this->assertSame("Vous devez accepter d'être contacté(e) par la plateforme En Marche !", $crawler->filter('#field-contacting-terms > .form__errors > li')->text());

        // Submit the committee form with valid data to create committee
        $this->client->submit($crawler->selectButton('Créer mon comité')->form([
            'committee' => [
                'name' => 'Lyon est En Marche !',
                'description' => 'Comité français En Marche ! de la ville de Lyon',
                'country' => 'FR',
                'postalCode' => '69001',
                'city' => '69001-69381',
                'facebookPageUrl' => 'https://www.facebook.com/EnMarcheLyon',
                'twitterNickname' => '@enmarchelyon',
                'googlePlusPageUrl' => 'https://plus.google.com/+EnMarcheavecEmmanuelMacron?hl=fr',
                'acceptConfidentialityTerms' => true,
                'acceptContactingTerms' => true,
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertInstanceOf(Committee::class, $committee = $this->committeeRepository->findMostRecentCommittee());
        $this->assertSame('Lyon est En Marche !', $committee->getName());
        $this->assertTrue($committee->isWaitingForApproval());
        $this->assertCount(1, $this->emailRepository->findMessages(CommitteeCreationConfirmationMessage::class, $emaiLAddress));

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Votre comité a été créé avec succès. Il est néanmoins en attente de validation par un administrateur', $crawler->filter('#notice-flashes')->text());
        $this->assertSame('Lyon est En Marche !', $crawler->filter('#committee-name')->text());
        $this->assertSame('Comité français En Marche ! de la ville de Lyon', $crawler->filter('#committee-description')->text());
    }

    public function provideRegularAdherentsCredentials()
    {
        return [
            ['carl999@example.fr', 'secret!12345'],
            ['luciole1989@spambox.fr', 'EnMarche2017'],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->committeeRepository = $this->getCommitteeRepository();
        $this->emailRepository = $this->getMailjetEmailRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->emailRepository = null;
        $this->committeeRepository = null;
        $this->container = null;
        $this->client = null;

        parent::tearDown();
    }
}
