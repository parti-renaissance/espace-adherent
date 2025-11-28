<?php

declare(strict_types=1);

namespace App\OAuth;

use App\Repository\OAuth\ClientRepository;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\InvalidParameterException;
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
        LoggerInterface $logger,
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
     * @throws InvalidParameterException
     */
    public function generateUrl(
        string $name,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ): string {
        $callbackParameters = $this->validateRedirectUriAndClient();

        return $this->urlGenerator->generate($name, $parameters + $callbackParameters, $referenceType);
    }

    private function validateRedirectUriAndClient(): array
    {
        if (!$request = $this->requestStack->getMainRequest()) {
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

        if (!$client = $this->clientRepository->findOneByUuid($clientUuid)) {
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
