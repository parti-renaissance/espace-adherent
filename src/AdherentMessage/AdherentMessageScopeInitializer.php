<?php

declare(strict_types=1);

namespace App\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Repository\MyTeam\MemberRepository;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AdherentMessageScopeInitializer
{
    public function __construct(
        private readonly ScopeGeneratorResolver $scopeResolver,
        private readonly MyTeamRepository $myTeamRepository,
        private readonly MemberRepository $memberRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function initializeFromScope(AdherentMessageInterface $message, bool $forceReset = false): void
    {
        $scope = $this->scopeResolver->generate();

        if (!$scope) {
            return;
        }

        if ($forceReset) {
            $message->setInstanceScope(null);
            $message->teamOwner = null;
            $message->senderRole = null;
            $message->setSender(null);
        }

        if (!$message->getInstanceScope()) {
            $message->setInstanceScope($scope->getMainCode());
        }

        if (!$message->teamOwner) {
            $message->teamOwner = $scope->getMainUser();
        }

        if (!$message->getSender() && !$scope->isNational()) {
            $message->setSender($scope->getMainUser());
        }

        $message->updateSenderDataFromScope($scope);

        if (
            $message->getSender()
            && ($team = $this->myTeamRepository->findOneByAdherentAndScope($teamOwner = $scope->getMainUser(), $scope->getMainCode()))
            && $teamOwner !== $message->getSender()
            && ($member = $this->memberRepository->findMemberInTeam($team, $message->getSender()))
        ) {
            $key = 'my_team_member.role.'.$member->getRole();
            $role = $this->translator->trans($key, ['gender' => $member->getAdherent()?->getGender()]);
            if ($role === $key) {
                $role = $member->getRole();
            }

            $message->senderRole = $role;
        }
    }
}
