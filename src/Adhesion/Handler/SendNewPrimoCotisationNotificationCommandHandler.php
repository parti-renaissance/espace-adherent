<?php

declare(strict_types=1);

namespace App\Adhesion\Handler;

use App\Adherent\Tag\TagEnum;
use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Command\SendNewPrimoCotisationNotificationCommand;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeRepository;
use App\Utils\StringCleaner;
use App\ValueObject\Genders;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Notifier\Bridge\Telegram\TelegramOptions;
use Symfony\Component\Notifier\ChatterInterface;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
class SendNewPrimoCotisationNotificationCommandHandler
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly CommitteeRepository $committeeRepository,
        private readonly ChatterInterface $chatter,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $telegramChatIdPrimoAdhesion,
    ) {
    }

    public function __invoke(SendNewPrimoCotisationNotificationCommand $command): void
    {
        /** @var Adherent $adherent */
        if (!$adherent = $this->adherentRepository->findOneByUuid($command->getUuid())) {
            return;
        }

        if (!$adherent->hasTag(TagEnum::getAdherentYearTag(tag: TagEnum::ADHERENT_YEAR_PRIMO_TAG_PATTERN))) {
            return;
        }

        $civility = match ($adherent->getGender()) {
            Genders::FEMALE => 'Mme ',
            Genders::MALE => 'M. ',
            default => '',
        };

        $zoneLines = [];

        if ($assemblyZone = $adherent->getAssemblyZone()) {
            $zoneLines[] = "{$assemblyZone->getCode()} - {$assemblyZone->getName()}";
        }

        if (
            !$adherent->isForeignResident()
            && (
                ($city = $adherent->getZonesOfType(Zone::BOROUGH))
                || ($city = $adherent->getZonesOfType(Zone::CITY))
            )
        ) {
            $city = current($city);
            $zoneLines[] = "{$city->getCode()} - {$city->getName()}";
        }

        if (($district = $adherent->getZonesOfType(Zone::DISTRICT)) || ($district = $adherent->getZonesOfType(Zone::FOREIGN_DISTRICT))) {
            $district = current($district);
            $code = explode('-', $district->getCode());
            $name = $district->isDistrict() ? $code[1].'e circonscription' : $district->getName();
            $zoneLines[] = "{$district->getCode()} - {$name}";
        }

        if ($committeeMembership = $adherent->getCommitteeMembership()) {
            $committee = $committeeMembership->getCommittee();
            $zoneLines[] = \sprintf('%s - %s', $committee->getAssemblyZone()?->getCode(), $committee->getName());
        }

        $zoneLines = StringCleaner::escapeMarkdown(implode("\n", $zoneLines));

        if (!$committeeMembership) {
            $zoneLines .= "\n_".(($committeesInAdherentZone = \count($this->committeeRepository->findInAdherentZone($adherent))) ? $committeesInAdherentZone.' comité\(s\) dans l\'Assemblée' : 'Aucun comité dans l\'Assemblée').'_';
        }

        $smsSubscriber = $adherent->hasSmsSubscriptionType() ? '✅' : '❌';
        $emailSubscriber = $adherent->isEmailSubscribed() ? '✅' : '❌';

        $step = AdhesionStepEnum::LABELS[AdhesionStepEnum::getLastFilledStep($adherent->isRenaissanceAdherent(), $adherent->getFinishedAdhesionSteps())] ?? '';
        $url = $this->urlGenerator->generate('admin_app_adherent_edit', ['id' => $adherent->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $fullName = StringCleaner::escapeMarkdown($adherent->getFullName());

        $chatMessage = new ChatMessage(
            <<<MESSAGE
                *{$civility}{$fullName}* \([{$adherent->getId()}]({$url})\)
                {$zoneLines}

                {$command->amount} €

                {$smsSubscriber} Abonné SMS
                {$emailSubscriber} Abonné Email

                Création du compte le {$adherent->getRegisteredAt()->format('d/m/Y')}
                {$step}
                MESSAGE,
            new TelegramOptions()
                ->chatId($this->telegramChatIdPrimoAdhesion)
                ->parseMode(TelegramOptions::PARSE_MODE_MARKDOWN_V2)
                ->disableWebPagePreview(true)
                ->disableNotification(true)
        );

        $this->chatter->send($chatMessage);
    }
}
