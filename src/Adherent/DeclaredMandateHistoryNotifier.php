<?php

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
        private readonly string $jemengageHost
    ) {
    }

    /**
     * @param array|Administrator[]          $administrators
     * @param array|DeclaredMandateHistory[] $declaredMandateHistories
     */
    public function notifyAdministrators(array $administrators, array $declaredMandateHistories): void
    {
        $this->transactionalMailer->sendMessage(RenaissanceDeclaredMandateNotificationMessage::create(
            array_map(function (Administrator $administrator): string {
                return $administrator->getEmailAddress();
            }, $administrators),
            $this->formatMandates($declaredMandateHistories),
            $this->generateAdminAdherentsUrl()
        ));
    }

    /**
     * @param array|string[]                 $recipients
     * @param array|DeclaredMandateHistory[] $declaredMandateHistories
     */
    public function notifyAdherents(array $recipients, array $declaredMandateHistories): void
    {
        $this->transactionalMailer->sendMessage(RenaissanceDeclaredMandateNotificationMessage::create(
            $recipients,
            $this->formatMandates($declaredMandateHistories),
            $this->generateJMEMilitantsUrl()
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

    private function generateAdminAdherentsUrl(): string
    {
        return $this->urlGenerator->generate('admin_app_adherent_list', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function generateJMEMilitantsUrl(): string
    {
        return '//'.$this->jemengageHost.'/militants';
    }
}
