<?php

namespace App\Security\Http\Session;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Marks a registration process starting as anonymous for any event or group,
 * including events, committees and citizen related subjects.
 *
 * The goal is to be able to redirect after login or account creation back to
 * the subject process (i.e following a committee).
 */
final class AnonymousFollowerSession
{
    // Either login or register path
    public const AUTHENTICATION_INTENTION = 'anonymous_authentication_intention';

    // The subject uri that the anonymous tried to reached before authentication (i.e following a committee)
    private const SESSION_KEY = 'app.anonymous_follower.callback_path';

    private const VALID_INTENTION_URLS = [
        '/connexion',
        '/adhesion',
    ];

    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function start(Request $request): ?RedirectResponse
    {
        if (!$request->query->has(self::AUTHENTICATION_INTENTION)) {
            return null;
        }

        $intentionUrl = $request->query->get(self::AUTHENTICATION_INTENTION);

        if (!$this->isValidIntention($intentionUrl)) {
            return null;
        }

        $this->session->set(self::SESSION_KEY, $this->buildIntentionUrl($request));

        return new RedirectResponse($intentionUrl);
    }

    public function isStarted(): bool
    {
        return $this->session->has(self::SESSION_KEY);
    }

    /**
     * @throws \LogicException If the session is not started
     */
    public function follow(string $callback): RedirectResponse
    {
        $follow = $this->terminate();

        $this->session->set(self::SESSION_KEY, $callback);

        return $follow;
    }

    /**
     * @throws \LogicException If the session is not started
     */
    public function terminate(): RedirectResponse
    {
        if (!$this->isStarted()) {
            throw new \LogicException('The event registration session is not started.');
        }

        return new RedirectResponse($this->session->remove(self::SESSION_KEY));
    }

    public function buildIntentionUrl(Request $request): string
    {
        $vars = $request->query->all();
        unset($vars[self::AUTHENTICATION_INTENTION]);

        return $request->getPathInfo().($vars ? '?'.\http_build_query($vars) : '');
    }

    private function isValidIntention(string $intentionUrl): bool
    {
        return \in_array($intentionUrl, self::VALID_INTENTION_URLS);
    }
}
