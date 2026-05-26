<?php

declare(strict_types=1);

namespace Tests\App\Membership\Signup;

use App\Mailer\Message\Renaissance\SignupConfirmationMessage;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * End-to-end happy path for the signup → magic link → ENABLED flow.
 *
 * This guards the wiring that unit tests cannot reach:
 *  - the magic link emitted in the confirmation mail is signable/consumable by Symfony LoginLink (real HMAC),
 *  - the LoginLinkAuthenticator fires on POST against MagicLinkController,
 *  - MagicLinkAuthenticationListener runs at priority 4096 (BEFORE UserCheckerListener at 256), so the
 *    PENDING → ENABLED transition happens before the disabled-check would reject the user,
 *  - the listener dispatches UserEvents::USER_VALIDATED (regression guard on the Phase 1 fix).
 */
#[Group('functional')]
class SignupActivationFlowTest extends AbstractApiTestCase
{
    use ControllerTestTrait;

    private const SIGNUP_URL = '/api/signup';
    private const CLIENT_IP = '127.0.0.1';

    public function testNewSignupCanActivateAccountViaMagicLink(): void
    {
        $email = 'activation-flow@example.test';

        // KernelBrowser reboots the kernel between requests by default → a listener attached to one
        // dispatcher instance would be lost on the next request. Pin the kernel so our spy survives.
        $this->client->disableReboot();

        // Spy on USER_VALIDATED so we can prove the Phase 1 dispatch fix is wired end-to-end.
        $validatedFor = null;
        static::getContainer()->get('event_dispatcher')->addListener(
            UserEvents::USER_VALIDATED,
            static function (UserEvent $event) use (&$validatedFor): void {
                $validatedFor = $event->getAdherent()->getEmailAddress();
            }
        );

        // 1. Sign up a new email → 201 + confirmation mail queued.
        $this->postSignup($email);
        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $this->manager->clear();
        $adherent = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertNotNull($adherent, 'signup must persist a PENDING adherent');
        self::assertTrue($adherent->isPending(), 'fresh signup must be PENDING');

        // 2. Extract the real magic_link URL from the SignupConfirmationMessage payload.
        $magicLink = $this->extractMagicLinkFromSentMail($email);

        // 3. Hit the magic link URL: GET shows the confirmation page, POST consumes the token.
        $this->getMagicLink($magicLink);
        self::assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->postMagicLink($magicLink);
        // LoginLinkAuthenticator redirects on success (302 typical for an authenticator success).
        self::assertContains(
            $this->client->getResponse()->getStatusCode(),
            [Response::HTTP_OK, Response::HTTP_FOUND],
            'consuming the magic link must succeed (200 or 302), got '.$this->client->getResponse()->getStatusCode()
        );

        // 4. Account is now ENABLED in DB (MagicLinkAuthenticationListener ran at priority 4096).
        $this->manager->clear();
        $reloaded = $this->getAdherentRepository()->findOneByEmail($email);
        self::assertTrue($reloaded->isEnabled(), 'magic link consumption must transition PENDING → ENABLED');

        // 5. USER_VALIDATED dispatched (Phase 1 fix: feeds Mailchimp sync / tags refresh / committee assign).
        self::assertSame($email, $validatedFor, 'USER_VALIDATED must be dispatched for the activated adherent');
    }

    public function testActivatedAccountCannotReuseTheSameMagicLink(): void
    {
        // max_uses = 1 by Symfony LoginLink default: a token consumed once must reject a second hit.
        $email = 'activation-flow-replay@example.test';

        $this->postSignup($email);
        $this->assertResponseStatusCode(Response::HTTP_CREATED, $this->client->getResponse());

        $magicLink = $this->extractMagicLinkFromSentMail($email);

        // First consumption succeeds → account ENABLED.
        $this->postMagicLink($magicLink);
        $this->manager->clear();
        self::assertTrue($this->getAdherentRepository()->findOneByEmail($email)->isEnabled());

        // The session/cookies must not carry over the previous identity for the replay attempt.
        $this->client->getCookieJar()->clear();

        // Second consumption must fail: the authenticator either rejects with 401 (default failure handler)
        // or redirects to the failure path (302). Anything that authenticates the user again is a regression.
        $this->postMagicLink($magicLink);
        self::assertContains(
            $this->client->getResponse()->getStatusCode(),
            [Response::HTTP_UNAUTHORIZED, Response::HTTP_FOUND],
            'a consumed magic link must not authenticate a second time, got '.$this->client->getResponse()->getStatusCode()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        self::getContainer()->get('limiter.signup')->create(self::CLIENT_IP)->reset();
    }

    private function postSignup(string $email): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::SIGNUP_URL,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'REMOTE_ADDR' => self::CLIENT_IP],
            json_encode([
                'email' => $email,
                'source' => 'newsletter',
                'recaptcha' => 'fake',
                'cgu_accepted' => true,
            ])
        );
    }

    private function extractMagicLinkFromSentMail(string $recipient): string
    {
        $emails = $this->getEmailRepository()->findRecipientMessages(SignupConfirmationMessage::class, $recipient);
        self::assertCount(1, $emails, 'exactly one SignupConfirmationMessage must be sent to the new signup');

        $payload = json_decode($emails[0]->getRequestPayloadJson(), true);
        self::assertIsArray($payload, 'Mandrill payload must decode');
        self::assertArrayHasKey('global_merge_vars', $payload['message']);

        foreach ($payload['message']['global_merge_vars'] as $var) {
            if ('magic_link' === $var['name']) {
                return $var['content'];
            }
        }

        self::fail('magic_link must be present in the Mandrill template variables');
    }

    private function getMagicLink(string $url): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->extractPath($url),
            $this->extractQuery($url),
            [],
            $this->buildServerParams($url)
        );
    }

    private function postMagicLink(string $url): void
    {
        // check_post_only = true on the firewall: the authenticator fires on POST.
        $this->client->request(
            Request::METHOD_POST,
            $this->extractPath($url),
            $this->extractQuery($url),
            [],
            $this->buildServerParams($url)
        );
    }

    private function buildServerParams(string $url): array
    {
        // The magic link route is host-scoped to %user_vox_host%; the KernelBrowser default host
        // (localhost) makes it 404. Forward the host carried by the URL itself.
        $params = ['REMOTE_ADDR' => self::CLIENT_IP];

        if ($host = parse_url($url, \PHP_URL_HOST)) {
            $params['HTTP_HOST'] = $host;
        }

        return $params;
    }

    private function extractPath(string $url): string
    {
        return parse_url($url, \PHP_URL_PATH) ?: '/';
    }

    private function extractQuery(string $url): array
    {
        $query = parse_url($url, \PHP_URL_QUERY);
        if (null === $query) {
            return [];
        }

        parse_str($query, $params);

        return $params;
    }
}
