<?php

namespace Tests\App\Controller\Renaissance\Security;

use App\DataFixtures\ORM\LoadAdherentData;
use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\Message\AdherentResetPasswordMessage;
use App\Mailer\Message\Renaissance\RenaissanceResetPasswordConfirmationMessage;
use App\Mailer\Message\Renaissance\RenaissanceResetPasswordMessage;
use App\Repository\AdherentRepository;
use App\Repository\EmailRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractRenaissanceWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('security')]
class SecurityControllerTest extends AbstractRenaissanceWebTestCase
{
    use ControllerTestTrait;

    /* @var AdherentRepository */
    private $adherentRepository;

    /* @var EmailRepository */
    private $emailRepository;

    #[DataProvider('getAdherentEmails')]
    public function testAuthenticationIsSuccessful(string $email, string $fullName, bool $isRenaissanceUser): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(0, $crawler->filter('#auth-error'));

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => $email,
            '_login_password' => LoadAdherentData::DEFAULT_PASSWORD,
        ]));

        $adherent = $this->adherentRepository->findOneByEmail($email);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(
            $isRenaissanceUser
                ? '/espace-adherent/evenements'
                : 'http://app.renaissance.code/',
            $this->client
        );
        $this->assertInstanceOf(\DateTime::class, $adherent->getLastLoggedAt());

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        if ($isRenaissanceUser) {
            $this->assertSame($fullName, trim($crawler->filter('h6')->first()->text()));
        }

        $this->client->click($crawler->selectLink('Me déconnecter')->link());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('http://app.renaissance.code/', $this->client);

        $crawler = $this->client->followRedirect();
        $this->assertSame(0, $crawler->selectLink($fullName)->count());
    }

    public static function getAdherentEmails(): array
    {
        return [
            ['renaissance-user-1@en-marche-dev.fr', 'Laure Fenix', true],
            ['carl999@example.fr', 'Carl Mirabeau', false],
        ];
    }

    #[DataProvider('provideInvalidCredentials')]
    public function testLoginCheckFails(string $username, string $password, string $messageExpected): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/connexion');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(0, $crawler->filter('#auth-error'));

        $this->client->submit($crawler->selectButton('Connexion')->form([
            '_login_email' => $username,
            '_login_password' => $password,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/connexion', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $error = $crawler->filter('.text-red-400'));
        $this->assertSame($messageExpected, trim($error->text()));
    }

    public static function provideInvalidCredentials(): array
    {
        return [
            'Unregistered adherent account' => [
                'foobar@foo.tld',
                'foo-bar-pass',
                'L\'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas.',
            ],
            'Registered enabled adherent' => [
                'carl999@example.fr',
                'foo-bar-pass',
                'L\'adresse e-mail et le mot de passe que vous avez saisis ne correspondent pas.',
            ],
            'Registered not validated account' => [
                'michelle.dufour@example.ch',
                'secret!12345',
                'Pour vous connecter vous devez confirmer votre adhésion. Si vous n\'avez pas reçu le mail de validation, vous pouvez cliquer ici pour le recevoir à nouveau.',
            ],
            'Registered disabled account' => [
                'simple-user-disabled@example.ch',
                'secret!12345',
                'Votre compte a été désactivé par un administrateur.',
            ],
        ];
    }

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
        $this->assertCount(1, $error = $crawler->filter('.text-red-500'));
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
        $this->assertClientIsRedirectedTo('http://app.renaissance.code/', $this->client);

        $this->client->followRedirect();

        $this->assertNotSame($this->getAdherentRepository()->findOneByEmail('michelle.dufour@example.ch')->getPassword(), $oldPassword);

        // Reset password twice
        $this->client->request(Request::METHOD_GET, $resetPasswordUrl);

        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    protected function setUp(): void
    {
        parent::setUp();

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
