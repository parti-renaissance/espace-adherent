<?php

namespace App\MyTeam;

use App\Entity\MyTeam\DelegatedAccess;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\DelegatedAccessCreatedMessage;
use App\Scope\GeneralScopeGenerator;
use App\Scope\ScopeEnum;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DelegatedAccessNotifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly GeneralScopeGenerator $generalScopeGenerator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function sendNewDelegatedAccessNotification(DelegatedAccess $delegatedAccess): void
    {
        $this->transactionalMailer->sendMessage(
            DelegatedAccessCreatedMessage::create(
                $delegatedAccess,
                implode(', ', $this->getZoneNames($delegatedAccess)),
                $this->translator->trans('role.'.$delegatedAccess->getType(), ['gender' => $delegatedAccess->getDelegator()->getGender()]),
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
            ScopeEnum::ANIMATOR => [$scope->getAttribute('dpt')],
            default => $scope->getZoneNames(),
        };
    }
}
