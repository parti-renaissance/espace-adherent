<?php

namespace AppBundle\Security\Http\Session;

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

        $this->session->set(self::SESSION_KEY, $request->getPathInfo());

        return new RedirectResponse($request->query->get(self::AUTHENTICATION_INTENTION));
    }

    public function isStarted(): bool
    {
        return $this->session->has(self::SESSION_KEY);
    }

    /**
     * @return RedirectResponse
     *
     * @throws \LogicException If the session is not started
     */
    public function follow(string $callback): RedirectResponse
    {
        $follow = $this->terminate();

        $this->session->set(self::SESSION_KEY, $callback);

        return $follow;
    }

    /**
     * @return RedirectResponse
     *
     * @throws \LogicException If the session is not started
     */
    public function terminate(): RedirectResponse
    {
        if (!$this->isStarted()) {
            throw new \LogicException('The event registration session is not started.');
        }

        return new RedirectResponse($this->session->remove(self::SESSION_KEY));
    }
}
