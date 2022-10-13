<?php

namespace Tests\App\Controller\EnMarche\Security;

use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\Message\AdherentResetPasswordMessage;
use App\Mailer\Message\Renaissance\RenaissanceResetPasswordConfirmationMessage;
use App\Mailer\Message\Renaissance\RenaissanceResetPasswordMessage;
use App\Repository\AdherentRepository;
use App\Repository\EmailRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group security
 */
class RenaissanceSecurityControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var AdherentRepository */
    private $adherentRepository;

    /* @var EmailRepository */
    private $emailRepository;

    public function testRetrieveForgotPasswordAction(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/mot-de-passe-oublie');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="form[email]"]'));
        $this->assertCount(0, $crawler->filter('.form__error'), 'No error should be displayed on initial display');
    }

    public function testRetrieveForgotPasswordActionWithEmptyEmail(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/mot-de-passe-oublie');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->selectButton('Envoyez-moi l\'email')->form(), ['form' => ['email' => '']]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('input[name="form[email]"]'));
        $this->assertCount(1, $error = $crawler->filter('.text-red'));
        $this->assertStringContainsString('Cette valeur ne doit pas être vide.', $error->text(), 'An empty email should be erroneous.');
    }

    public function testRetrieveForgotPasswordActionWithUnknownEmail(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/mot-de-passe-oublie');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $formData = [
            'form' => ['email' => 'toto@example.org'],
        ];

        $this->client->submit($crawler->selectButton('Envoyez-moi l\'email')->form(), $formData);

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(0, $crawler->filter('.form__error'));
        $this->assertStringContainsString('Si l\'adresse que vous avez saisie est valide, un e-mail vous a été envoyé contenant un lien pour réinitialiser votre mot de passe.', $crawler->text());
        $this->assertCount(0, $this->emailRepository->findRecipientMessages(AdherentResetPasswordMessage::class, 'toto@example.org'), 'No mail should have been sent to unknown account.');
    }

    public function testRetrieveForgotPasswordActionWithKnownEmailSendEmail(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/mot-de-passe-oublie');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $formData = [
            'form' => ['email' => 'carl999@example.fr'],
        ];

        $this->client->submit($crawler->selectButton('Envoyez-moi l\'email')->form(), $formData);

        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(0, $crawler->filter('.form__error'));
        $this->assertStringContainsString('Si l\'adresse que vous avez saisie est valide, un e-mail vous a été envoyé contenant un lien pour réinitialiser votre mot de passe.', $crawler->text());

        $this->assertCount(1, $this->emailRepository->findRecipientMessages(RenaissanceResetPasswordMessage::class, 'carl999@example.fr'), 'An email should have been sent.');
    }

    public function testResetPasswordAction(): void
    {
        $adherent = $this->getAdherentRepository()->findOneByEmail('michelle.dufour@example.ch');
        $token = $this->getAdherentResetPasswordToken($adherent);
        $oldPassword = $adherent->getPassword();

        $this->assertNull($token->getUsageDate());

        $resetPasswordUrl = sprintf('/changer-mot-de-passe/%s/%s', $adherent->getUuid(), $token->getValue());
        $crawler = $this->client->request(Request::METHOD_GET, $resetPasswordUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('input[name="adherent_reset_password[password][first]"]'));
        $this->assertCount(1, $crawler->filter('input[name="adherent_reset_password[password][second]"]'));

        $this->client->submit($crawler->selectButton('adherent_reset_password[submit]')->form(), [
            'adherent_reset_password' => [
                'password' => [
                    'first' => 'new password',
                    'second' => 'new password',
                ],
            ],
        ]);

        $this->assertCount(1, $this->emailRepository->findRecipientMessages(RenaissanceResetPasswordConfirmationMessage::class, 'michelle.dufour@example.ch'), 'A confirmation email should have been sent.');
        $this->assertClientIsRedirectedTo('http://test.renaissance.code/', $this->client);

        $this->client->followRedirect();

        $this->assertNotSame($this->getAdherentRepository()->findOneByEmail('michelle.dufour@example.ch')->getPassword(), $oldPassword);

        // Reset password twice
        $this->client->request(Request::METHOD_GET, $resetPasswordUrl);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->client->setServerParameter('HTTP_HOST', self::$container->getParameter('renaissance_host'));

        $this->adherentRepository = $this->getAdherentRepository();
        $this->emailRepository = $this->getEmailRepository();
    }

    protected function tearDown(): void
    {
        $this->emailRepository = null;
        $this->adherentRepository = null;

        parent::tearDown();
    }

    private function getAdherentResetPasswordToken(Adherent $adherent): AdherentResetPasswordToken
    {
        return $this->getResetPasswordTokenRepository()->findOneBy(['adherentUuid' => $adherent->getUuidAsString()]);
    }
}
