<?php

namespace App\Twig;

use App\AppCodeEnum;
use App\Repository\OAuth\ClientRepository;
use Twig\Extension\RuntimeExtensionInterface;

class OAuthClientRuntime implements RuntimeExtensionInterface
{
    private ClientRepository $clientRepository;
    private ?string $clientId = null;

    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function getJMEClientId(): ?string
    {
        if (null !== $this->clientId) {
            return $this->clientId;
        }

        $clients = $this->clientRepository->findBy(['code' => AppCodeEnum::JEMENGAGE_WEB]);

        return $this->clientId = (\count($clients) >= 1 ? $clients[0]->getUuid()->toString() : '');
    }
}
