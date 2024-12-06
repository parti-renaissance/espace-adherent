<?php

namespace App\MyTeam;

use App\Entity\MyTeam\DelegatedAccess;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\DelegatedAccessCreatedMessage;
use App\Scope\GeneralScopeGenerator;
use App\Scope\ScopeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DelegatedAccessNotifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly GeneralScopeGenerator $generalScopeGenerator,
    ) {
    }

    public function sendNewDelegatedAccessNotification(DelegatedAccess $delegatedAccess): void
    {
        $this->transactionalMailer->sendMessage(
            DelegatedAccessCreatedMessage::create(
                $delegatedAccess,
                implode(', ', $this->getZoneNames($delegatedAccess)),
                $this->urlGenerator->generate('vox_app'),
            )
        );
    }

    private function getZoneNames(DelegatedAccess $delegatedAccess): array
    {
        $delegator = $delegatedAccess->getDelegator();
        $scope = $this
            ->generalScopeGenerator
            ->getGenerator(
                $delegatedAccess->getType(),
                $delegator
            )
            ->generate($delegator)
        ;

        return match ($scope->getCode()) {
            ScopeEnum::ANIMATOR => [$scope->getAttributes()['dpt']],
            default => $scope->getZoneNames(),
        };
    }
}
