<?php

namespace App\OAuth\Grant;

use App\Entity\Device;
use App\Repository\DeviceRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Grant\ClientCredentialsGrant as BaseClientCredentialsGrant;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

class ClientCredentialsGrant extends BaseClientCredentialsGrant
{
    /**
     * @var DeviceRepository
     */
    private $deviceRepository;

    public function setDeviceRepository(DeviceRepository $deviceRepository): void
    {
        $this->deviceRepository = $deviceRepository;
    }

    protected function validateDevice(ServerRequestInterface $request): ?Device
    {
        if (!$deviceId = $this->getRequestParameter('device_id', $request)) {
            return null;
        }

        if (!Uuid::isValid($deviceId)) {
            throw OAuthServerException::invalidRequest('Device id is not a valid UUID');
        }

        if (!$device = $this->deviceRepository->findOneByUuid($deviceId)) {
            $device = new Device(Uuid::fromString($deviceId));
            $this->deviceRepository->save($device);
        }

        return $device;
    }

    /**
     * {@inheritdoc}
     */
    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        \DateInterval $accessTokenTTL
    ) {
        // Validate request
        $client = $this->validateClient($request);
        $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));
        $device = $this->validateDevice($request);

        // Finalize the requested scopes
        $finalizedScopes = $this->scopeRepository->finalizeScopes($scopes, $this->getIdentifier(), $client);

        // Issue and persist access token
        $accessToken = $this->issueAccessToken(
            $accessTokenTTL,
            $client,
            $device ? $device->getUuid()->toString() : null,
            $finalizedScopes
        );

        // Inject access token into response type
        $responseType->setAccessToken($accessToken);

        return $responseType;
    }

    protected function issueAccessToken(
        \DateInterval $accessTokenTTL,
        ClientEntityInterface $client,
        $userIdentifier,
        array $scopes = []
    ) {
        $maxGenerationAttempts = self::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;

        $accessToken = $this->accessTokenRepository->getNewToken($client, $scopes, $userIdentifier);

        $accessToken->setClient($client);
        $accessToken->setDeviceIdentifier($userIdentifier);
        $accessToken->setExpiryDateTime((new \DateTime())->add($accessTokenTTL));

        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }

        while ($maxGenerationAttempts-- > 0) {
            $accessToken->setIdentifier($this->generateUniqueIdentifier());
            try {
                $this->accessTokenRepository->persistNewAccessToken($accessToken);

                return $accessToken;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if (0 === $maxGenerationAttempts) {
                    throw $e;
                }
            }
        }
    }
}
