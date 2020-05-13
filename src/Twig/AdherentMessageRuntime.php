<?php

namespace App\Twig;

use App\AdherentMessage\AdherentMessageTypeEnum;
use App\AdherentMessage\MessageLimiter;
use App\Entity\Adherent;
use App\Repository\AdherentMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\RuntimeExtensionInterface;

class AdherentMessageRuntime implements RuntimeExtensionInterface
{
    private $repository;
    private $limiter;

    public function __construct(AdherentMessageRepository $repository, MessageLimiter $limiter)
    {
        $this->repository = $repository;
        $this->limiter = $limiter;
    }

    public function getSentMessageCount(Request $request, Adherent $adherent, string $messageType): ?int
    {
        switch ($messageType) {
            case AdherentMessageTypeEnum::COMMITTEE:
                if (!$request->attributes->has('committee')) {
                    return 0;
                }

                return $this->repository->countTotalCommitteeMessage(
                    $adherent,
                    $request->attributes->get('committee'),
                    true
                );

            case AdherentMessageTypeEnum::CITIZEN_PROJECT:
                if (!$request->attributes->has('citizenProject')) {
                    return 0;
                }

                return $this->repository->countTotalCitizenProjectMessage(
                    $adherent,
                    $request->attributes->get('citizenProject'),
                    true
                );

            default:
                return $this->repository->countTotalMessage($adherent, $messageType, true);
        }
    }

    public function getMessageLimit(string $messageType): ?int
    {
        return $this->limiter->getLimit($messageType);
    }
}
