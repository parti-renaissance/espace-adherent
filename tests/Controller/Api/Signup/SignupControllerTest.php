<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Signup;

use App\Entity\Adherent;
use App\Entity\AdherentSignupSource;
use App\Entity\PostAddress;
use App\Entity\SignupSource;
use App\Mailer\Message\Renaissance\AdhesionAlreadyAdherentMessage;
use App\Mailer\Message\Renaissance\AdhesionAlreadySympathizerMessage;
use App\Mailer\Message\Renaissance\SignupConfirmationMessage;
use App\Mailer\Message\Renaissance\SignupExcludedAdherentMessage;
use App\Membership\ActivityPositionsEnum;
use App\Subscription\SubscriptionTypeEnum;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class SignupControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;

    private const URL = '/api/signup';
    private const CLIENT_IP = '127.0.0.1';

    public function testSignupUnknownEmailReturns201(): void
    {
        $email = 'signed-up-new-contact@example.test';

        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertNotNull($adherent);
        self::assertTrue($adherent->isPending());
        // Passwordless: no password is set on a signup account.
        self::assertNull($adherent->getPassword());
        self::assertNotNull($this->findSignupSource($adherent, 'newsletter'));
    }

    public function testSignupCreatesFullProfileWithOptIns(): void
    {
        $email = 'full-profile-signup@example.test';

        $this->post([
            'email' => $email,
            'source' => 'newsletter',
            'civility' => 'female',
            'first_name' => 'Alice',
            'last_name' => 'Martin',
            'phone' => '+33612345678',
            'postal_code' => '75008',
            'city_name' => 'Paris',
            'email_opt_in' => true,
            'sms_opt_in' => true,
            'recaptcha' => 'fake',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertNotNull($adherent);
        self::assertTrue($adherent->isPending());
        self::assertSame('female', $adherent->getGender());
        self::assertSame('Alice', $adherent->getFirstName());
        self::assertSame('Martin', $adherent->getLastName());
        // Partial address (zip + city, no country) is stored via createFlexible (country defaulted to FR).
        self::assertSame('75008', $adherent->getPostAddress()->getPostalCode());
        self::assertSame('Paris', $adherent->getPostAddress()->getCityName());
        // Opt-ins recorded as SubscriptionType (canonical consent, with EmailSubscriptionHistory trail).
        self::assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL));
        self::assertTrue($adherent->hasSubscriptionType(SubscriptionTypeEnum::MILITANT_ACTION_SMS));
        self::assertNotNull($this->findSignupSource($adherent, 'newsletter'));
    }

    public function testSignupWithoutOptInsDoesNotSubscribe(): void
    {
        $email = 'no-optin-signup@example.test';

        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertFalse($adherent->hasSubscriptionType(SubscriptionTypeEnum::MOVEMENT_INFORMATION_EMAIL));
        self::assertFalse($adherent->hasSubscriptionType(SubscriptionTypeEnum::MILITANT_ACTION_SMS));
    }

    public function testSignupKnownEmailDoesNotOverwriteIdentity(): void
    {
        $email = 'carl999@example.fr';
        $originalFirstName = $this->getAdherentRepository()->findOneByEmail($email)->getFirstName();

        $this->post([
            'email' => $email,
            'source' => 'petition',
            'first_name' => 'Overwrite',
            'last_name' => 'Attempt',
            'recaptcha' => 'fake',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);
        // The existing identity must not be overwritten by the signup payload.
        self::assertSame($originalFirstName, $adherent->getFirstName());
        self::assertNotNull($this->findSignupSource($adherent, 'petition'));
    }

    public function testRejectsMissingCguReturns400(): void
    {
        $email = 'no-cgu@example.test';

        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake', 'general_opt_in' => false]);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
    }

    public function testStringFalseGeneralOptInDoesNotBypassGate(): void
    {
        $email = 'string-false-cgu@example.test';

        // A malicious client sends the boolean as the JSON string "false" to probe type coercion.
        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake', 'general_opt_in' => 'false']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
    }

    public function testRejectsInvalidCivilityReturns400(): void
    {
        $email = 'bad-civility@example.test';

        $this->post(['email' => $email, 'source' => 'newsletter', 'civility' => 'not_a_gender', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
    }

    public function testRejectsSmsOptInWithoutPhoneReturns400(): void
    {
        $email = 'sms-no-phone@example.test';

        $this->post(['email' => $email, 'source' => 'newsletter', 'sms_opt_in' => true, 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
    }

    public function testRejectsMalformedJsonReturns400(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            '{"email": "broken", '
        );

        // Malformed payload must be a clean 400, never a 500.
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function testRejectsInvalidEmailReturns400(): void
    {
        $this->post(['email' => 'not-an-email', 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
    }

    public function testRejectsUnknownSourceReturns400(): void
    {
        $this->post(['email' => 'unknown-source@example.test', 'source' => 'does_not_exist', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertStringContainsString('source', (string) $this->client->getResponse()->getContent());
        self::assertNull($this->getAdherentRepository()->findOneByEmail('unknown-source@example.test'));
    }

    public function testRejectsDisabledSourceReturns400(): void
    {
        $source = $this->manager->getRepository(SignupSource::class)->findOneBy(['code' => 'event']);
        $source->enabled = false;
        $this->manager->flush();

        $this->post(['email' => 'disabled-source@example.test', 'source' => 'event', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertStringContainsString('source', (string) $this->client->getResponse()->getContent());
        self::assertNull($this->getAdherentRepository()->findOneByEmail('disabled-source@example.test'));
    }

    public function testRejectsInvalidCaptchaReturns400(): void
    {
        $this->post(['email' => 'bad-captcha@example.test', 'source' => 'newsletter', 'recaptcha' => 'wrong_answer']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertStringContainsString('recaptcha', (string) $this->client->getResponse()->getContent());
        self::assertNull($this->getAdherentRepository()->findOneByEmail('bad-captcha@example.test'));
    }

    public function testSignupBannedEmailReturns201AndSendsExcludedMail(): void
    {
        // disabled-email@test.com is the fixture banned email (LoadBannedAdherentData).
        $email = 'disabled-email@test.com';

        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        // No new adherent must be created for a banned email.
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
        // The banned email must receive an informative "excluded" mail.
        $this->assertCountMails(1, SignupExcludedAdherentMessage::class, $email);
        // No confirmation mail must be sent to a banned email.
        $this->assertCountMails(0, SignupConfirmationMessage::class, $email);
    }

    public function testSignupExistingActiveSendsMagicLinkAndLogsSource(): void
    {
        // carl999@example.fr is an existing ENABLED Renaissance sympathizer fixture.
        $email = 'carl999@example.fr';

        $this->post(['email' => $email, 'source' => 'vox', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        // Sympathizer branch: the existing account is invited to log in via magic link.
        $this->assertCountMails(1, AdhesionAlreadySympathizerMessage::class, $email);
        $this->assertCountMails(0, SignupConfirmationMessage::class, $email);
        $this->assertCountMails(0, SignupExcludedAdherentMessage::class, $email);
        // The signup source must still be recorded for analytics.
        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertNotNull($this->findSignupSource($adherent, 'vox'));
    }

    public function testSignupDisabledAdherentReturns201Silently(): void
    {
        // DISABLED is a soft deactivation (user left or admin flag), not a disciplinary exclusion:
        // the response must be silent (no mail) to avoid enumeration leaks and false negative wording.
        $email = 'disabled-signup-target@example.test';
        $this->createAdherentWithStatus($email, Adherent::DISABLED);

        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        $this->assertCountMails(0, SignupExcludedAdherentMessage::class, $email);
        $this->assertCountMails(0, SignupConfirmationMessage::class, $email);
    }

    public function testSignupToDeleteAdherentReturns201Silently(): void
    {
        $email = 'todelete-signup-target@example.test';
        $this->createAdherentWithStatus($email, Adherent::TO_DELETE);

        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        // Deletion in progress: no mail of any kind must be sent (no excluded, no confirmation, no magic link).
        $this->assertCountMails(0, SignupExcludedAdherentMessage::class, $email);
        $this->assertCountMails(0, SignupConfirmationMessage::class, $email);
    }

    public function testSignupNewEmailReturns201AndSendsConfirmation(): void
    {
        $email = 'new-signup-with-mail@example.test';

        $this->post(['email' => $email, 'source' => 'newsletter', 'first_name' => 'Alice', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        // The new adherent receives the lightweight confirmation mail (magic link only, no OTP).
        $this->assertCountMails(1, SignupConfirmationMessage::class, $email);
        $this->assertCountMails(0, SignupExcludedAdherentMessage::class, $email);

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertNotNull($adherent);
        self::assertTrue($adherent->isPending());
    }

    public function testSignupWithVoxSourceReturns201(): void
    {
        $email = 'vox-source-signup@example.test';

        $this->post(['email' => $email, 'source' => 'vox', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertNotNull($adherent);
        self::assertNotNull($this->findSignupSource($adherent, 'vox'));
    }

    public function testRejectsEmptyEmailReturns400(): void
    {
        // Empty email must be rejected (NotBlank constraint), with no side effects.
        $this->post(['email' => '', 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail(''));
        $this->assertCountMails(0, SignupConfirmationMessage::class);
        $this->assertCountMails(0, SignupExcludedAdherentMessage::class);
    }

    public function testRejectsMissingEmailReturns400(): void
    {
        // The payload omits the email field entirely.
        $this->post(['source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $this->assertCountMails(0, SignupConfirmationMessage::class);
    }

    public function testInvalidEmailFormatReturns400WithNoMailSent(): void
    {
        // Validation failures must short-circuit before any mail can be sent.
        $this->post(['email' => 'not-an-email', 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $this->assertCountMails(0, SignupConfirmationMessage::class);
        $this->assertCountMails(0, SignupExcludedAdherentMessage::class);
    }

    public function testSignupExistingPendingSendsMagicLink(): void
    {
        // PENDING is a fully active branch for routing: the user must be invited to log in,
        // not asked to "sign up again". Without an explicit fixture, build one inline.
        $email = 'pending-existing@example.test';
        $this->createAdherentWithStatus($email, Adherent::PENDING);

        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        // A bare PENDING account has no "adherent" tag → falls into the sympathizer-style mail.
        $this->assertCountMails(1, AdhesionAlreadySympathizerMessage::class, $email);
        $this->assertCountMails(0, SignupConfirmationMessage::class, $email);
        $this->assertCountMails(0, SignupExcludedAdherentMessage::class, $email);
    }

    public function testSignupExistingRenaissanceAdherentSendsAdherentMessage(): void
    {
        // michelle.dufour@example.ch is an ENABLED Renaissance adherent (tag adherent:a_jour_*).
        // This branch sends AdhesionAlreadyAdherentMessage (different from sympathizer mail).
        $email = 'michelle.dufour@example.ch';

        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        $this->assertCountMails(1, AdhesionAlreadyAdherentMessage::class, $email);
        $this->assertCountMails(0, AdhesionAlreadySympathizerMessage::class, $email);
        $this->assertCountMails(0, SignupConfirmationMessage::class, $email);
    }

    public function testRejectsDisposableEmailReturns400(): void
    {
        // Disposable domains (e.g. 10minutemail.com) are blocklisted by StrictEmail (disposable=true default).
        // The signup must reject before any side effect.
        $email = 'noise@10minutemail.com';

        $this->post(['email' => $email, 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
        $this->assertCountMails(0, SignupConfirmationMessage::class, $email);
    }

    public function testRejectsMissingSourceReturns400(): void
    {
        // The source field is required (#[Assert\NotBlank]).
        // We omit it entirely (distinct from "unknown source" already covered).
        $email = 'no-source-signup@example.test';

        $this->client->request(
            Request::METHOD_POST,
            self::URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            json_encode(['email' => $email, 'recaptcha' => 'fake', 'general_opt_in' => true])
        );

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
    }

    public function testSignupNormalizesEmailCase(): void
    {
        // mb_strtolower normalises the email before lookup: signing up with an upper-cased email
        // for an existing lower-cased account must still hit the "existing active" branch,
        // not create a duplicate PENDING account.
        $uppercaseEmail = 'CARL999@EXAMPLE.FR';
        $normalisedEmail = 'carl999@example.fr';

        $this->post(['email' => $uppercaseEmail, 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        // Mail recipient is the stored (lower-cased) email, regardless of payload casing.
        $this->assertCountMails(1, AdhesionAlreadySympathizerMessage::class, $normalisedEmail);
        $this->assertCountMails(0, SignupConfirmationMessage::class, $normalisedEmail);
        // No duplicate adherent is created.
        $this->manager->clear();
        self::assertNotNull($this->getAdherentRepository()->findOneByEmail($normalisedEmail));
    }

    public function testRejectsInvalidPhoneNumberReturns400(): void
    {
        // AssertPhoneNumber rejects malformed numbers at deserialisation/validation.
        $email = 'bad-phone@example.test';

        $this->post([
            'email' => $email,
            'source' => 'newsletter',
            'phone' => 'not-a-phone-number',
            'recaptcha' => 'fake',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
    }

    public function testRejectsInvalidCountryReturns400(): void
    {
        // #[Assert\Country] rejects unknown ISO country codes.
        $email = 'bad-country@example.test';

        $this->post([
            'email' => $email,
            'source' => 'newsletter',
            'country' => 'ZZ',
            'postal_code' => '75001',
            'city_name' => 'Paris',
            'recaptcha' => 'fake',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
    }

    public function testRejectsTooLongFirstNameReturns400(): void
    {
        // #[Assert\Length(max: 50)] on firstName. 51 chars must be rejected.
        $email = 'long-firstname@example.test';

        $this->post([
            'email' => $email,
            'source' => 'newsletter',
            'first_name' => str_repeat('A', 51),
            'recaptcha' => 'fake',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        self::assertNull($this->getAdherentRepository()->findOneByEmail($email));
    }

    public function testRateLimitReturns429(): void
    {
        for ($i = 1; $i <= 5; ++$i) {
            $this->post(['email' => "rate-limit-$i@example.test", 'source' => 'newsletter', 'recaptcha' => 'fake']);
            $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());
        }

        $this->post(['email' => 'rate-limit-over@example.test', 'source' => 'newsletter', 'recaptcha' => 'fake']);

        $this->assertResponseStatusCode(Response::HTTP_TOO_MANY_REQUESTS, $this->client->getResponse());
    }

    protected function setUp(): void
    {
        parent::setUp();

        // The limiter storage (apcu) persists across kernel boots within the test process: reset per test.
        self::getContainer()->get('limiter.signup')->create(self::CLIENT_IP)->reset();
    }

    private function post(array $payload): void
    {
        // The CGU gate requires general_opt_in to be true: default it so success cases pass; rejection
        // tests override it with false.
        $payload += ['general_opt_in' => true];

        $this->client->request(
            Request::METHOD_POST,
            self::URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            json_encode($payload)
        );
    }

    private function findSignupSource(Adherent $adherent, string $source): ?AdherentSignupSource
    {
        return $this->manager->getRepository(AdherentSignupSource::class)->findOneBy([
            'adherent' => $adherent,
            'source' => $source,
        ]);
    }

    private function createAdherentWithStatus(string $email, string $status): Adherent
    {
        $adherent = Adherent::create(
            Adherent::createUuid($email),
            substr(bin2hex(random_bytes(4)), 0, 7), // public_id column is length 7
            $email,
            null,
            'female',
            'Jane',
            'Doe',
            new \DateTime('1990-01-01'),
            ActivityPositionsEnum::EMPLOYED,
            PostAddress::createFrenchAddress('1 rue de Paris', '75001-75101'),
        );
        $adherent->setStatus($status);

        $this->manager->persist($adherent);
        $this->manager->flush();

        return $adherent;
    }
}
