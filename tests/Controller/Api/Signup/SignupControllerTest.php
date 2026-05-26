<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Signup;

use App\Entity\Adherent;
use App\Entity\AdherentSignupSource;
use App\Entity\SignupSource;
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
}
