<?php

declare(strict_types=1);

namespace App\AdherentMessage\TransactionalMessage\MessageModifier;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Repository\ScopeRepository;

class ScopeTargetThemeModifier implements MessageModifierInterface
{
    private const SCOPE_TARGET_THEME = [
        'primary' => '#714991',
        'soft' => '#F8F0FF',
        'hover' => '#4F296D',
        'active' => '#5E397C',
    ];

    public function __construct(private readonly ScopeRepository $scopeRepository)
    {
    }

    public function support(AdherentMessageInterface $message): bool
    {
        return null !== $message->getFilter();
    }

    public function modify(AdherentMessageInterface $message): void
    {
        if (!empty($message->getFilter()->scopeTargets)) {
            $message->senderTheme = self::SCOPE_TARGET_THEME;

            return;
        }

        if (
            ($code = $message->getInstanceScope())
            && ($scope = $this->scopeRepository->findOneByCode($code))
        ) {
            $message->senderTheme = $scope->getTheme();
        }
    }
}
