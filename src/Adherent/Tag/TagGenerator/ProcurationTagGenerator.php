<?php

namespace App\Adherent\Tag\TagGenerator;

use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Repository\Procuration\ProxyRepository;
use App\Repository\Procuration\RequestRepository;

class ProcurationTagGenerator extends AbstractTagGenerator
{
    public function __construct(
        private readonly ProxyRepository $proxyRepository,
        private readonly RequestRepository $requestRepository,
    ) {
    }

    public function generate(Adherent $adherent, array $previousTags): array
    {
        $tags = [];

        if ($this->proxyRepository->hasUpcomingProxy($adherent)) {
            $tags[] = TagEnum::PROCURATION_PROXY;
        }

        if ($this->requestRepository->hasUpcomingRequest($adherent)) {
            $tags[] = TagEnum::PROCURATION_REQUEST;
        }

        return $tags;
    }
}
