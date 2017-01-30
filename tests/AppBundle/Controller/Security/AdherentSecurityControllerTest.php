<?php

namespace Tests\AppBundle\Controller\Security;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Mailjet\Message\AdherentResetPasswordMessage;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\MailjetEmailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

class AdherentSecurityControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /* @var AdherentRepository */
    private $adherentRepository;

    /* @var MailjetEmailRepository */
    private $emailRepository;

    public function testAuthenticationIsSuccessful()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('form[name="app_login"]'));
        $this->assertCount(0, $crawler->filter('.login__error'));

        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_adherent_email' => 'carl999@example.fr',
            '_adherent_password' => 'secret!12345',
        ]));

        $adherent = $this->adherentRepository->findByEmail('carl999@example.fr');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/evenements', $this->client, true);
        $this->assertInstanceOf(\DateTimeImmutable::class, $adherent->getLastLoggedAt());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(2, $crawler->selectLink('Carl Mirabeau')->count());

        $this->client->click($crawler->selectLink('Déconnexion')->link());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/', $this->client, true);

        $crawler = $this->client->followRedirect();
        $this->assertSame(0, $crawler->selectLink('Carl Mirabeau')->count());
    }

    /**
     * @dataProvider provideInvalidCredentials
     */
    public function testLoginCheckFails($username, $password)
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('form[name="app_login"]'));
        $this->assertCount(0, $crawler->filter('.login__error'));

        $this->client->submit($crawler->selectButton('Je me connecte')->form([
            '_adherent_email' => $username,
            '_adherent_password' => $password,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/espace-adherent/connexion', $this->client, true);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $error = $crawler->filter('.login__error'));
        $this->assertSame('Identifiants invalides.', trim($error->text()));
    }

    public function provideInvalidCredentials()
    {
        return [
            'Unregistered adherent account' => [
                'foobar@foo.tld',
                'foo-bar-pass',
            ],
            'Registered enabled adherent' => [
                'carl999@example.fr',
                'foo-bar-pass',
            ],
            'Registered disabled account' => [
                'michelle.dufour@example.ch',
                'secret!12345',
            ],
        ];
    }

    public function testRetrieveForgotPasswordAction()
    {
        $client = $this->makeClient();

        $crawler = $client->request(Request::METHOD_GET, '/espace-adherent/mot-de-passe-oublie');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="form[email]"]'));
        $this->assertCount(0, $crawler->filter('.form__error'), 'No error should be displayed on initial display');
    }

    public function testRetrieveForgotPasswordActionWithEmptyEmail()
    {
        $client = $this->makeClient();

        $crawler = $client->request(Request::METHOD_GET, '/espace-adherent/mot-de-passe-oublie');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $crawler = $client->submit($crawler->selectButton('form[submit]')->form(), ['form' => ['email' => '']]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="form[email]"]'));
        $this->assertCount(1, $error = $crawler->filter('.form__error'));
        $this->assertContains('Cette valeur ne doit pas être vide.', $error->text(), 'An empty email should be erroneous.');
    }

    public function testRetrieveForgotPasswordActionWithUnknownEmail()
    {
        $client = $this->makeClient();

        $crawler = $client->request(Request::METHOD_GET, '/espace-adherent/mot-de-passe-oublie');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $formData = [
            'form' => ['email' => 'toto@example.org'],
        ];

        $crawler = $client->submit($crawler->selectButton('form[submit]')->form(), $formData);

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="form[email]"]'));
        $this->assertCount(0, $crawler->filter('.form__error'));
        $this->assertContains('Un e-mail vous a été envoyé contenant un lien pour réinitialiser votre mot de passe.', $crawler->text());
        $this->assertCount(0, $this->emailRepository->findMessages(AdherentResetPasswordMessage::class, 'toto@example.org'), 'No mail should have been sent to unknown account.');
    }

    public function testRetrieveForgotPasswordActionWithKnownEmailSendEmail()
    {
        $client = $this->makeClient();

        $crawler = $client->request(Request::METHOD_GET, '/espace-adherent/mot-de-passe-oublie');

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $formData = [
            'form' => ['email' => 'michelle.dufour@example.ch'],
        ];

        $crawler = $client->submit($crawler->selectButton('form[submit]')->form(), $formData);

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="form[email]"]'));
        $this->assertCount(0, $crawler->filter('.form__error'));
        $this->assertContains('Un e-mail vous a été envoyé contenant un lien pour réinitialiser votre mot de passe.', $crawler->text());

        $this->assertCount(1, $this->emailRepository->findMessages(AdherentResetPasswordMessage::class, 'michelle.dufour@example.ch'), 'An email should have been sent.');
    }

    public function testResetPasswordAction()
    {
        $client = $this->client = $this->makeClient();
        $adherent = $this->getAdherentRepository()->findByEmail('michelle.dufour@example.ch');
        $token = $this->getFirstAdherentResetPasswordToken();
        $oldPassword = $adherent->getPassword();

        $this->assertNull($token->getUsageDate());

        $resetPasswordUrl = sprintf('/espace-adherent/changer-mot-de-passe/%s/%s', $adherent->getUuid(), $token->getValue());
        $crawler = $client->request(Request::METHOD_GET, $resetPasswordUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $client->getResponse());
        $this->assertCount(1, $crawler->filter('input[name="adherent_reset_password[password][first]"]'));
        $this->assertCount(1, $crawler->filter('input[name="adherent_reset_password[password][second]"]'));

        $client->submit($crawler->selectButton('adherent_reset_password[submit]')->form(), [
            'adherent_reset_password' => [
                'password' => [
                    'first' => 'new password',
                    'second' => 'new password',
                ],
            ],
        ]);

        $this->assertClientIsRedirectedTo('/espace-adherent/mon-profil', $client);

        $client->followRedirect();

        // Refresh the adherent
        $this->getEntityManager(Adherent::class)->refresh($adherent);

        $this->assertNotSame($adherent->getPassword(), $oldPassword);

        // Reset password twice
        $client->request(Request::METHOD_GET, $resetPasswordUrl);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
        $this->loadFixtures([
            LoadAdherentData::class,
        ]);
        $this->adherentRepository = $this->getAdherentRepository();
        $this->emailRepository = $this->getMailjetEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();
        $this->loadFixtures([]);
        $this->emailRepository = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }

    private function getFirstAdherentResetPasswordToken()
    {
        return current($this->getResetPasswordTokenRepository()->findAll());
    }
}
