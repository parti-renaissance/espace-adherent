<?php

declare(strict_types=1);

namespace App\Controller\OAuth;

use App\OAuth\Oidc\Exception\InvalidIdTokenHintException;
use App\OAuth\Oidc\IdTokenHintValidator;
use App\Repository\AdherentRepository;
use App\Repository\OAuth\ClientRepository;
use App\Repository\OAuth\RefreshTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route(path: '/end-session', name: 'app_oidc_end_session', methods: ['GET'])]
class OidcEndSessionController extends AbstractController
{
    public function __construct(
        private readonly IdTokenHintValidator $idTokenHintValidator,
        private readonly ClientRepository $clientRepository,
        private readonly AdherentRepository $adherentRepository,
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $idTokenHint = $request->query->get('id_token_hint');
        $postLogoutRedirectUri = $request->query->get('post_logout_redirect_uri');
        $state = $request->query->get('state');

        $validatedHint = null;
        if (\is_string($idTokenHint) && '' !== $idTokenHint) {
            try {
                $validatedHint = $this->idTokenHintValidator->validate($idTokenHint);
            } catch (InvalidIdTokenHintException) {
                return $this->logoutAndRedirectToLogin($request);
            }
        }

        if (null === $validatedHint) {
            return $this->logoutAndRedirectToLogin($request);
        }

        $client = $this->clientRepository->findOneByUuid($validatedHint->clientUuid);
        $user = $this->adherentRepository->findOneByUuid($validatedHint->userUuid);

        if (null !== $client && null !== $user) {
            $this->refreshTokenRepository->revokeTokensByUserAndClient($user, $client);
        }

        if (!\is_string($postLogoutRedirectUri) || '' === $postLogoutRedirectUri) {
            return $this->logoutAndRedirectToLogin($request);
        }

        if (null === $client || !$client->hasPostLogoutRedirectUri($postLogoutRedirectUri)) {
            return $this->logoutAndRedirectToLogin($request);
        }

        $this->terminateSession($request);

        $target = $postLogoutRedirectUri;
        if (\is_string($state) && '' !== $state) {
            $target .= (str_contains($target, '?') ? '&' : '?').'state='.rawurlencode($state);
        }

        return new RedirectResponse($target);
    }

    private function logoutAndRedirectToLogin(Request $request): RedirectResponse
    {
        $this->tokenStorage->setToken(null);
        $this->addFlash('info', 'oidc.logout.confirmed');

        return $this->redirectToRoute('app_renaissance_login');
    }

    private function terminateSession(Request $request): void
    {
        $session = $request->getSession();
        if ($session->isStarted()) {
            $session->invalidate();
        }
        $this->tokenStorage->setToken(null);
    }
}
