<?php

namespace App\Security\Http\Session;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Marks a registration process starting as anonymous for any event or group,
 * including events and committees related subjects.
 *
 * The goal is to be able to redirect after login or account creation back to
 * the subject process (i.e following a committee).
 */
class AnonymousFollowerSession
{
    // Either login or register path
    public const AUTHENTICATION_INTENTION = 'callback';

    // The subject uri that the anonymous tried to reach before authentication (i.e. following a committee)
    public const SESSION_KEY = 'app.anonymous_follower.callback_path';

    private const VALID_INTENTION_URLS = [
        '/connexion',
        '/adhesion',
    ];

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public function start(Request $request): ?RedirectResponse
    {
        if (!$request->query->has(self::AUTHENTICATION_INTENTION)) {
            return null;
        }

        $intentionUrl = $request->query->get(self::AUTHENTICATION_INTENTION);

        if (!$this->isValidIntention(parse_url($intentionUrl, \PHP_URL_PATH))) {
            return null;
        }

        $this->requestStack->getSession()->set(self::SESSION_KEY, $this->buildIntentionUrl($request));

        return new RedirectResponse($intentionUrl);
    }

    public function isStarted(): bool
    {
        return $this->requestStack->getSession()->has(self::SESSION_KEY);
    }

    public function getCallback(): ?string
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY);
    }

    public function setCallback(string $callback): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, $callback);
    }

    /**
     * @throws \LogicException If the session is not started
     */
    public function follow(string $callback): RedirectResponse
    {
        $follow = $this->terminate();

        $this->requestStack->getSession()->set(self::SESSION_KEY, $callback);

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

        return new RedirectResponse($this->requestStack->getSession()->remove(self::SESSION_KEY));
    }

    public function buildIntentionUrl(Request $request): string
    {
        $vars = $request->query->all();
        unset($vars[self::AUTHENTICATION_INTENTION]);

        return $request->getPathInfo().($vars ? '?'.http_build_query($vars) : '');
    }

    private function isValidIntention(string $intentionUrl): bool
    {
        return \in_array($intentionUrl, self::VALID_INTENTION_URLS);
    }
}
