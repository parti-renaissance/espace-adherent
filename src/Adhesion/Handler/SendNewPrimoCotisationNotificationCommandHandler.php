<?php

namespace App\Adhesion\Handler;

use App\Adherent\Tag\TagEnum;
use App\Adhesion\AdhesionStepEnum;
use App\Adhesion\Command\SendNewPrimoCotisationNotificationCommand;
use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
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

        if ($city = $adherent->getZonesOfType(Zone::CITY)) {
            $city = current($city);
            $firstPostalCode = $city->getPostalCode()[0] ?? null;
            $zoneLines[] = "{$firstPostalCode} - {$city->getName()}";
        }

        if ($district = $adherent->getZonesOfType(Zone::DISTRICT)) {
            $district = current($district);
            $code = explode('-', $district->getCode());
            $zoneLines[] = "{$district->getCode()} - {$code[1]}e circonscription";
        }

        if ($committeeMembership = $adherent->getCommitteeV2Membership()) {
            $committee = $committeeMembership->getCommittee();
            $zoneLines[] = \sprintf('%s - %s', $committee->getAssemblyZone()?->getCode(), $committee->getName());
        }

        $zoneLines = implode("\n", $zoneLines);

        $smsSubscriber = $adherent->hasSmsSubscriptionType() ? '✅' : '❌';
        $emailSubscriber = $adherent->isEmailSubscribed() ? '✅' : '❌';

        $step = AdhesionStepEnum::LABELS[AdhesionStepEnum::getLastFilledStep($adherent->isRenaissanceAdherent(), $adherent->getFinishedAdhesionSteps())] ?? '';
        $url = $this->urlGenerator->generate('admin_app_adherent_edit', ['id' => $adherent->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        $chatMessage = new ChatMessage(
            <<<MESSAGE
                *{$civility}{$adherent->getFullName()}* ([{$adherent->getId()}]({$url}))
                {$zoneLines}

                {$command->amount} €

                {$smsSubscriber} Abonné SMS
                {$emailSubscriber} Abonné Email

                Création du compte le {$adherent->getRegisteredAt()->format('d/m/Y')}
                {$step}
                MESSAGE,
            (new TelegramOptions())
                ->chatId($this->telegramChatIdPrimoAdhesion)
                ->parseMode(TelegramOptions::PARSE_MODE_MARKDOWN)
                ->disableWebPagePreview(true)
                ->disableNotification(true)
        );

        $this->chatter->send($chatMessage);
    }
}
