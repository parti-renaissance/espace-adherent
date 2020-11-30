<?php

namespace App\OAuth\Grant;

use App\Entity\Device;
use App\OAuth\Model\AccessToken;
use App\Repository\DeviceRepository;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

trait DeviceGrantTrait
{
    /**
     * @var DeviceRepository
     */
    protected $deviceRepository;

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

        if (!$device = $this->deviceRepository->findOneByDeviceUuid($deviceId)) {
            $device = new Device(Uuid::uuid4(), Uuid::fromString($deviceId));
            $this->deviceRepository->save($device);
        }

        return $device;
    }

    protected function issueAccessTokenWithDevice(
        \DateInterval $accessTokenTTL,
        ClientEntityInterface $client,
        ?string $userIdentifier,
        ?string $deviceIdentifier,
        array $scopes = []
    ) {
        $maxGenerationAttempts = self::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;

        /** @var AccessToken $accessToken */
        $accessToken = $this->accessTokenRepository->getNewToken($client, $scopes, $userIdentifier);

        $accessToken->setClient($client);
        $accessToken->setUserIdentifier($userIdentifier);
        $accessToken->setDeviceIdentifier($deviceIdentifier);
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
