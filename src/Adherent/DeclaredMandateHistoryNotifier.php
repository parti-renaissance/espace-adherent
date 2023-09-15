<?php

namespace App\Adherent;

use App\Entity\Adherent;
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
        private readonly string $jemengageHost
    ) {
    }

    /**
     * @param array|DeclaredMandateHistory[] $declaredMandateHistories
     */
    public function notifyAdministrator(Administrator $administrator, array $declaredMandateHistories): void
    {
        $this->transactionalMailer->sendMessage(RenaissanceDeclaredMandateNotificationMessage::createForAdministrator(
            $administrator,
            $this->formatMandates($declaredMandateHistories),
            $this->urlGenerator->generate('admin_app_adherent_list', [], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }

    /**
     * @param array|DeclaredMandateHistory[] $declaredMandateHistories
     */
    public function notifyAdherent(Adherent $adherent, array $declaredMandateHistories): void
    {
        $this->transactionalMailer->sendMessage(RenaissanceDeclaredMandateNotificationMessage::createForAdherent(
            $adherent,
            $this->formatMandates($declaredMandateHistories),
            '//'.$this->jemengageHost.'/adherents'
        ));
    }

    /**
     * @param array|DeclaredMandateHistory[] $declaredMandateHistories
     */
    private function formatMandates(array $declaredMandateHistories): array
    {
        $formattedMandates = [];

        foreach ($declaredMandateHistories as $declaredMandateHistory) {
            $formattedMandates[] = [
                'adherent_name' => $declaredMandateHistory->getAdherent()->getFullName(),
                'added_mandates' => implode(', ', $this->translateMandates($declaredMandateHistory->getAddedMandates())),
                'removed_mandates' => implode(', ', $this->translateMandates($declaredMandateHistory->getRemovedMandates())),
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
}
