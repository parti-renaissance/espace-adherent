<?php

declare(strict_types=1);

namespace Tests\App\Controller\Api\Signup;

use App\Adhesion\ActivationCodeManager;
use App\DataFixtures\ORM\LoadClientData;
use App\Entity\Adherent;
use App\Entity\AdherentActivationCode;
use App\Entity\PostAddress;
use App\Membership\ActivityPositionsEnum;
use App\Membership\Signup\SignupCode;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class SignupActivateControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;

    private const URL = '/api/signup/activate';
    private const CLIENT_IP = '127.0.0.1';
    // Valid PKCE verifier (43-128 chars, RFC 7636 charset); the challenge is its S256 hash.
    private const CODE_VERIFIER = 'fixed-test-code-verifier-0123456789-0123456789-0123456789';
    // The calling app's client_id + a redirect_uri registered for it (CLIENT_13 fixture). Both are
    // now sent by the front and bind the minted code: they must match at the /oauth/token exchange.
    private const CLIENT_ID = LoadClientData::CLIENT_13_UUID;
    private const REDIRECT_URI = 'http://localhost:8081';

    public function testActivateValidCodeReturnsAuthorizationCodeAndEnablesAdherent(): void
    {
        $adherent = $this->createPendingAdherent('activate-success@example.test');
        $code = $this->generateCode($adherent);

        $this->post([
            'email' => $adherent->getEmailAddress(),
            'code' => $code->value,
            'code_challenge' => self::codeChallenge(),
            'client_id' => self::CLIENT_ID,
            'redirect_uri' => self::REDIRECT_URI,
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // A standard OAuth authorization code is handed back so the app can exchange it on
        // /oauth/v2/token (with its own pinned redirect_uri + code_verifier) without a browser.
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertIsArray($body);
        self::assertArrayHasKey('code', $body);
        self::assertNotEmpty($body['code']);
        self::assertArrayNotHasKey('redirect_uri', $body, 'redirect_uri must NOT be echoed: the app sends its own pinned value.');

        $this->manager->clear();
        $reloaded = $this->getAdherentRepository()->findOneByEmail($adherent->getEmailAddress());
        self::assertTrue($reloaded->isEnabled(), 'PENDING → ENABLED transition must happen on a valid code.');
    }

    public function testActivationAuthorizationCodeCanBeExchangedForTokens(): void
    {
        // End-to-end: the minted code must actually yield tokens via the standard
        // authorization_code grant + PKCE verifier (no browser, no client secret).
        $adherent = $this->createPendingAdherent('activate-exchange@example.test');
        $code = $this->generateCode($adherent);

        $this->post([
            'email' => $adherent->getEmailAddress(),
            'code' => $code->value,
            'code_challenge' => self::codeChallenge(),
            'client_id' => self::CLIENT_ID,
            'redirect_uri' => self::REDIRECT_URI,
        ]);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $authorizationCode = json_decode((string) $this->client->getResponse()->getContent(), true)['code'];

        $this->client->request(Request::METHOD_POST, '/oauth/v2/token', [
            'client_id' => self::CLIENT_ID,
            'grant_type' => 'authorization_code',
            'redirect_uri' => self::REDIRECT_URI,
            'code' => $authorizationCode,
            'code_verifier' => self::CODE_VERIFIER,
        ]);

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_OK, $response);
        $tokens = json_decode((string) $response->getContent(), true);
        self::assertNotEmpty($tokens['access_token'] ?? null, 'The minted code must exchange into an access token.');
        self::assertNotEmpty($tokens['refresh_token'] ?? null);
    }

    public function testActivationCodeIsUselessWithoutTheMatchingPkceVerifier(): void
    {
        // The whole point of PKCE here: an intercepted code cannot be exchanged without the
        // verifier held only by the legitimate app. A wrong verifier must be rejected.
        $adherent = $this->createPendingAdherent('activate-pkce-guard@example.test');
        $code = $this->generateCode($adherent);

        $this->post([
            'email' => $adherent->getEmailAddress(),
            'code' => $code->value,
            'code_challenge' => self::codeChallenge(),
            'client_id' => self::CLIENT_ID,
            'redirect_uri' => self::REDIRECT_URI,
        ]);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $authorizationCode = json_decode((string) $this->client->getResponse()->getContent(), true)['code'];

        $this->client->request(Request::METHOD_POST, '/oauth/v2/token', [
            'client_id' => self::CLIENT_ID,
            'grant_type' => 'authorization_code',
            'redirect_uri' => self::REDIRECT_URI,
            'code' => $authorizationCode,
            'code_verifier' => 'a-wrong-verifier-0123456789-0123456789-0123456789',
        ]);

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $response);
        $body = json_decode((string) $response->getContent(), true);
        self::assertArrayNotHasKey('access_token', $body ?? [], 'A mismatched PKCE verifier must not yield a token.');
    }

    public function testActivationCodeRequiresAVerifierAtExchange(): void
    {
        // Definitive proof the challenge is actually bound to the code: exchanging WITHOUT a
        // verifier must be rejected. This only happens when the code carries a code_challenge
        // (league requires the verifier then) — it would succeed if the challenge were dropped.
        $adherent = $this->createPendingAdherent('activate-pkce-bound@example.test');
        $code = $this->generateCode($adherent);

        $this->post([
            'email' => $adherent->getEmailAddress(),
            'code' => $code->value,
            'code_challenge' => self::codeChallenge(),
            'client_id' => self::CLIENT_ID,
            'redirect_uri' => self::REDIRECT_URI,
        ]);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $authorizationCode = json_decode((string) $this->client->getResponse()->getContent(), true)['code'];

        // No code_verifier at all.
        $this->client->request(Request::METHOD_POST, '/oauth/v2/token', [
            'client_id' => self::CLIENT_ID,
            'grant_type' => 'authorization_code',
            'redirect_uri' => self::REDIRECT_URI,
            'code' => $authorizationCode,
        ]);

        $response = $this->client->getResponse();
        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $response);
        $body = json_decode((string) $response->getContent(), true);
        self::assertArrayNotHasKey('access_token', $body ?? [], 'A code missing its verifier must not yield a token.');
    }

    public function testActivateValidCodeWithoutChallengeEnablesButReturnsNoCode(): void
    {
        // PKCE is mandatory for the auto-login code: without a challenge the account is still
        // activated, but no authorization code is handed back (no silent non-PKCE downgrade).
        $adherent = $this->createPendingAdherent('activate-no-challenge@example.test');
        $code = $this->generateCode($adherent);

        $this->post(['email' => $adherent->getEmailAddress(), 'code' => $code->value]);

        $this->assertResponseStatusCode(Response::HTTP_NO_CONTENT, $this->client->getResponse());
        self::assertEmpty((string) $this->client->getResponse()->getContent());

        $this->manager->clear();
        self::assertTrue($this->getAdherentRepository()->findOneByEmail($adherent->getEmailAddress())->isEnabled());
    }

    public function testActivateWithChallengeButWithoutClientReturnsBadRequest(): void
    {
        // Opting into the auto-login flow (challenge present) but omitting client_id/redirect_uri is a
        // malformed request: it must fail loud with a 400 so a front integration bug surfaces instead
        // of silently yielding no code. The account is still activated, and the error code is distinct
        // from the activation error so the front does NOT replay the single-use code.
        $adherent = $this->createPendingAdherent('activate-no-client@example.test');
        $code = $this->generateCode($adherent);

        $this->post([
            'email' => $adherent->getEmailAddress(),
            'code' => $code->value,
            'code_challenge' => self::codeChallenge(),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['error' => 'invalid_authorization_request'], $body);

        // The malformed auto-login params must not roll back the activation itself.
        $this->manager->clear();
        self::assertTrue($this->getAdherentRepository()->findOneByEmail($adherent->getEmailAddress())->isEnabled());
    }

    public function testActivateWithUnknownClientIdEnablesButReturnsNoCode(): void
    {
        // A well-formed but unknown client_id cannot mint a code (no client to bind it to), yet the
        // account is still activated — a misconfigured front must not block activation.
        $adherent = $this->createPendingAdherent('activate-unknown-client@example.test');
        $code = $this->generateCode($adherent);

        $this->post([
            'email' => $adherent->getEmailAddress(),
            'code' => $code->value,
            'code_challenge' => self::codeChallenge(),
            'client_id' => 'ffffffff-ffff-4fff-8fff-ffffffffffff',
            'redirect_uri' => self::REDIRECT_URI,
        ]);

        $this->assertResponseStatusCode(Response::HTTP_NO_CONTENT, $this->client->getResponse());
        self::assertEmpty((string) $this->client->getResponse()->getContent());

        $this->manager->clear();
        self::assertTrue($this->getAdherentRepository()->findOneByEmail($adherent->getEmailAddress())->isEnabled());
    }

    public function testActivateWrongCodeReturnsUniformError(): void
    {
        $adherent = $this->createPendingAdherent('activate-wrong-code@example.test');
        $this->generateCode($adherent);

        $this->post(['email' => $adherent->getEmailAddress(), 'code' => '000']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['error' => 'invalid_or_expired'], $body);

        // No transition for an invalid code.
        $this->manager->clear();
        self::assertTrue($this->getAdherentRepository()->findOneByEmail($adherent->getEmailAddress())->isPending());
    }

    public function testActivateUnknownEmailReturnsUniformError(): void
    {
        $this->post(['email' => 'never-signed-up@example.test', 'code' => '123']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['error' => 'invalid_or_expired'], $body);
    }

    public function testActivateEnabledAdherentReturnsUniformError(): void
    {
        // An already ENABLED adherent must not be re-activated via the public endpoint.
        $email = 'activate-already-enabled@example.test';
        $adherent = $this->createPendingAdherent($email);
        $adherent->enable();
        $this->manager->flush();
        $this->generateCode($adherent);

        $this->post(['email' => $email, 'code' => '123']);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['error' => 'invalid_or_expired'], $body);
    }

    public function testActivateInvalidCodeFormatReturnsRfc7807Violation(): void
    {
        $adherent = $this->createPendingAdherent('activate-invalid-format@example.test');
        $this->generateCode($adherent);

        // 4 digits instead of 3 — the regex must reject it before any DB lookup.
        // MapRequestPayload validation failures default to 422 (Symfony convention).
        $this->post(['email' => $adherent->getEmailAddress(), 'code' => '1234']);

        $this->assertResponseStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse());
        SignupApiErrorAssertions::assertValidationErrorShape($this->client->getResponse());
    }

    public function testActivateMalformedJsonReturnsRfc7807BadRequest(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            '{"email": "broken", '
        );

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        SignupApiErrorAssertions::assertBadRequestErrorShape($this->client->getResponse());
    }

    public function testActivateRateLimitedByIp(): void
    {
        // signup_code_attempt = 10/min : the 11th request from the same IP must be 429.
        for ($i = 1; $i <= 10; ++$i) {
            $this->post(['email' => 'rate-limit-loop@example.test', 'code' => '000']);
            $status = $this->client->getResponse()->getStatusCode();
            self::assertNotSame(Response::HTTP_TOO_MANY_REQUESTS, $status, "Iteration {$i} must not be rate-limited yet.");
        }

        $this->post(['email' => 'rate-limit-loop@example.test', 'code' => '000']);
        $this->assertResponseStatusCode(Response::HTTP_TOO_MANY_REQUESTS, $this->client->getResponse());
    }

    public function testActivateExhaustedPerAccountRetryBudgetRejectsEvenValidCode(): void
    {
        // activation_account_retry = 3/min, keyed by adherent UUID and consumed inside checkCode()
        // BEFORE the code lookup. createPendingAdherent() resets it, so this adherent starts with a
        // full quota of 3. The IP limiter (10/min) stays well under, so it cannot explain a rejection.
        $adherent = $this->createPendingAdherent('activate-account-limiter@example.test');
        $code = $this->generateCode($adherent);

        // Three wrong attempts exhaust the per-account budget (failedAttempts stays < 5, so the code
        // itself is NOT revoked — only the account retry budget is spent).
        for ($i = 1; $i <= 3; ++$i) {
            $this->post(['email' => $adherent->getEmailAddress(), 'code' => '000']);
            $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        }

        // The 4th attempt carries the VALID code: only the per-account limiter can reject it here
        // (the IP limiter allows 10/min). A 400 + still-PENDING adherent proves the limiter is wired.
        $this->post(['email' => $adherent->getEmailAddress(), 'code' => $code->value]);

        $this->assertResponseStatusCode(Response::HTTP_BAD_REQUEST, $this->client->getResponse());
        $body = json_decode((string) $this->client->getResponse()->getContent(), true);
        self::assertSame(['error' => 'invalid_or_expired'], $body);

        $this->manager->clear();
        self::assertTrue(
            $this->getAdherentRepository()->findOneByEmail($adherent->getEmailAddress())->isPending(),
            'A valid code presented after the per-account retry budget is exhausted must NOT enable the account.'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        // The signup_code_attempt limiter persists across kernel boots within the same test process.
        self::getContainer()->get('limiter.signup_code_attempt')->create(self::CLIENT_IP)->reset();
    }

    private function post(array $payload): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            json_encode($payload)
        );
    }

    private function createPendingAdherent(string $email): Adherent
    {
        $adherent = Adherent::create(
            Adherent::createUuid($email),
            substr(bin2hex(random_bytes(4)), 0, 7),
            $email,
            null,
            'female',
            'Jane',
            'Doe',
            new \DateTime('1990-01-01'),
            ActivityPositionsEnum::EMPLOYED,
            PostAddress::createFrenchAddress('1 rue de Paris', '75001-75101'),
        );
        $adherent->setStatus(Adherent::PENDING);

        $this->manager->persist($adherent);
        $this->manager->flush();

        // Reset the per-adherent retry limiter so a fresh adherent starts with a full quota.
        self::getContainer()
            ->get('limiter.activation_account_retry')
            ->create('activation_code.validate.'.$adherent->getUuidAsString())
            ->reset()
        ;

        return $adherent;
    }

    private static function codeChallenge(): string
    {
        // S256 PKCE challenge = base64url(sha256(verifier)), no padding.
        return rtrim(strtr(base64_encode(hash('sha256', self::CODE_VERIFIER, true)), '+/', '-_'), '=');
    }

    private function generateCode(Adherent $adherent): AdherentActivationCode
    {
        return self::getContainer()->get(ActivationCodeManager::class)->generate(
            $adherent,
            force: true,
            codeLength: SignupCode::LENGTH,
        );
    }
}
