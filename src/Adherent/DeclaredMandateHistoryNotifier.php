<?php

declare(strict_types=1);

namespace App\Adherent;

use App\Entity\Administrator;
use App\Entity\Reporting\DeclaredMandateHistory;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceDeclaredMandateNotificationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeclaredMandateHistoryNotifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly TranslatorInterface $translator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $jemengageHost,
    ) {
    }

    /**
     * @param array|Administrator[]          $administrators
     * @param array|DeclaredMandateHistory[] $declaredMandateHistories
     */
    public function notifyAdministrators(array $administrators, array $declaredMandateHistories): void
    {
        $notifications = $this->aggregateHistories($declaredMandateHistories);

        if (empty($notifications)) {
            return;
        }

        $this->transactionalMailer->sendMessage(RenaissanceDeclaredMandateNotificationMessage::create(
            array_map(function (Administrator $administrator): string {
                return $administrator->getEmailAddress();
            }, $administrators),
            $this->formatMandates($notifications),
            $this->generateAdminAdherentsUrl()
        ));
    }

    /**
     * @param array|string[]                 $recipients
     * @param array|DeclaredMandateHistory[] $declaredMandateHistories
     */
    public function notifyAdherents(array $recipients, array $declaredMandateHistories): void
    {
        $notifications = $this->aggregateHistories($declaredMandateHistories);

        if (empty($notifications)) {
            return;
        }

        $this->transactionalMailer->sendMessage(RenaissanceDeclaredMandateNotificationMessage::create(
            $recipients,
            $this->formatMandates($notifications),
            $this->generateJMEMilitantsUrl()
        ));
    }

    private function aggregateHistories(array $declaredMandatesHistories): array
    {
        $groupedByAdherent = [];
        foreach ($declaredMandatesHistories as $declaredMandatesHistory) {
            $adherent = $declaredMandatesHistory->getAdherent();

            $groupedByAdherent[$adherent->getId()][] = $declaredMandatesHistory;
        }

        $aggregated = [];
        /** @var array|DeclaredMandateHistory[] $adherentHistories */
        foreach ($groupedByAdherent as $adherentHistories) {
            $adherent = null;
            $addedMandates = [];
            $removedMandates = [];

            foreach ($adherentHistories as $adherentHistory) {
                if (!$adherent) {
                    $adherent = $adherentHistory->getAdherent();
                }

                $addedMandates = array_diff(
                    array_unique(array_merge($addedMandates, $adherentHistory->getAddedMandates())),
                    $adherentHistory->getRemovedMandates()
                );
                $removedMandates = array_diff(
                    array_unique(array_merge($removedMandates, $adherentHistory->getRemovedMandates())),
                    $adherentHistory->getAddedMandates()
                );
            }

            if ($adherent && (!empty($addedMandates) || !empty($removedMandates))) {
                $aggregated[] = new DeclaredMandateNotification($adherent, $addedMandates, $removedMandates);
            }
        }

        return $aggregated;
    }

    /**
     * @param array|DeclaredMandateNotification[] $declaredMandateNotifications
     */
    private function formatMandates(array $declaredMandateNotifications): array
    {
        $formattedMandates = [];

        foreach ($declaredMandateNotifications as $declaredMandateNotification) {
            $formattedMandates[] = [
                'adherent_name' => $declaredMandateNotification->adherent->getFullName(),
                'added_mandates' => implode(', ', $this->translateMandates($declaredMandateNotification->addedMandates)),
                'removed_mandates' => implode(', ', $this->translateMandates($declaredMandateNotification->removedMandates)),
            ];
        }

        return $formattedMandates;
    }

    private function translateMandates(array $mandates): array
    {
        return array_map(function (string $mandate): string {
            return $this->translator->trans("adherent.mandate.type.$mandate");
        }, $mandates);
    }

    private function generateAdminAdherentsUrl(): string
    {
        return $this->urlGenerator->generate('admin_app_adherent_list', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function generateJMEMilitantsUrl(): string
    {
        return $this->jemengageHost.'/militants';
    }
}
