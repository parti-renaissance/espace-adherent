<?php

namespace App\OAuth;

use App\Repository\OAuth\ClientRepository;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CallbackManager
{
    private $urlGenerator;
    private $requestStack;
    private $clientRepository;
    private $logger;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        ClientRepository $clientRepository,
        LoggerInterface $logger
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
        $this->clientRepository = $clientRepository;
        $this->logger = $logger;
    }

    /**
     * Generate an URL with redirect_uri and client_id appended to the given route $name
     * if and only if these values are valid, which means :
     *   - client_id (query string) belongs to an OAuth app
     *   - redirect_uri (query string) is supported by this client_id.
     *
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function generateUrl(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $callbackParameters = $this->validateRedirectUriAndClient();

        return $this->urlGenerator->generate($name, $parameters + $callbackParameters, $referenceType);
    }

    /**
     * Generate a redirect response to redirect_uri provided in request query string
     * if and only if these values are valid, which means :
     *   - client_id (query string) belongs to an OAuth app
     *   - redirect_uri (query string) is supported by this client_id.
     *
     * It falls back to $fallback route name if request information are not valid
     *
     * @throws \Symfony\Component\Routing\Exception\InvalidParameterException
     */
    public function redirectToClientIfValid(string $fallback = null, $fallbackParameters = []): RedirectResponse
    {
        $callbackParameters = $this->validateRedirectUriAndClient();

        return new RedirectResponse($callbackParameters['redirect_uri'] ?? $this->urlGenerator->generate($fallback ?: 'homepage', $fallbackParameters));
    }

    private function validateRedirectUriAndClient(): array
    {
        if (!$request = $this->requestStack->getMasterRequest()) {
            return [];
        }

        $redirectUri = $request->query->get('redirect_uri');
        $clientId = $request->query->get('client_id');

        if (!$redirectUri || !$clientId) {
            return [];
        }

        $callbackParameters = [
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
        ];

        try {
            $clientUuid = Uuid::fromString($clientId);
        } catch (InvalidUuidStringException $e) {
            $this->logger->warning("Client provides an invalid UUID \"$clientId\"", ['exception' => $e, $callbackParameters]);

            return [];
        }

        if (!$client = $this->clientRepository->findClientByUuid($clientUuid)) {
            $this->logger->warning("Client \"$clientId\" cannot be found in DB", $callbackParameters);

            return [];
        }

        if (!$client->hasRedirectUri($redirectUri)) {
            $this->logger->warning("Client \"$clientId\"; redirect_uri \"$redirectUri\" does not match any of the supported redirect URIs of this client");

            return [];
        }

        return $callbackParameters;
    }
}
