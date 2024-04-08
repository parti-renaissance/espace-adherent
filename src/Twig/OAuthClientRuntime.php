<?php

namespace App\Twig;

use App\AppCodeEnum;
use App\Repository\OAuth\ClientRepository;
use Twig\Extension\RuntimeExtensionInterface;

class OAuthClientRuntime implements RuntimeExtensionInterface
{
    private array $clientIds = [];

    public function __construct(private readonly ClientRepository $clientRepository)
    {
    }

    public function getJMEClientId(): ?string
    {
        return $this->getClientId(AppCodeEnum::JEMENGAGE_WEB);
    }

    public function getVoxClientId(): ?string
    {
        return $this->getClientId(AppCodeEnum::VOX);
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
