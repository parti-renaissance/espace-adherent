<?php

declare(strict_types=1);

namespace App\Twig;

use App\AppCodeEnum;
use App\Entity\OAuth\Client;
use App\Repository\OAuth\ClientRepository;
use Twig\Extension\RuntimeExtensionInterface;

class OAuthClientRuntime implements RuntimeExtensionInterface
{
    private array $clientIds = [];

    public function __construct(private readonly ClientRepository $clientRepository)
    {
    }

    public function getVoxClient(): ?Client
    {
        $code = AppCodeEnum::BESOIN_D_EUROPE;

        if (!empty($this->clientIds[$code])) {
            return $this->clientIds[$code];
        }

        return $this->clientIds[$code] = $this->clientRepository->getVoxClient();
    }

    private function getClientId(string $code): ?string
    {
        if (isset($this->clientIds[$code])) {
            return $this->clientIds[$code];
        }

        $clients = $this->clientRepository->findBy(['code' => $code]);

        return $this->clientIds[$code] = (\count($clients) >= 1 ? $clients[0]->getUuid()->toString() : '');
    }
}
