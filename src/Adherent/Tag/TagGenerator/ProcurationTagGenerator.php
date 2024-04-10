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
        private readonly RequestRepository $requestRepository
    ) {
    }

    public function generate(Adherent $adherent, array $previousTags): array
    {
        $tags = [];

        if (0 < $this->proxyRepository->count(['adherent' => $adherent])) {
            $tags[] = TagEnum::PROCURATION_PROXY;
        }

        if (0 < $this->requestRepository->count(['adherent' => $adherent])) {
            $tags[] = TagEnum::PROCURATION_REQUEST;
        }

        return $tags;
    }
}
