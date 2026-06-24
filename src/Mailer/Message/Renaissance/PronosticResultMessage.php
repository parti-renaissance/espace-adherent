<?php

declare(strict_types=1);

namespace App\Mailer\Message\Renaissance;

use App\Entity\Pronostic\Pronostic;
use App\Entity\Pronostic\PronosticParticipation;
use App\Mailer\Message\Message;
use Symfony\Component\Uid\Uuid;

final class PronosticResultMessage extends AbstractRenaissanceMessage
{
    /**
     * @param PronosticParticipation[] $participations
     */
    public static function create(Pronostic $pronostic, array $participations): Message
    {
        if (!$participations) {
            throw new \InvalidArgumentException('At least one participation is required.');
        }

        $participation = array_shift($participations);
        if (!$participation instanceof PronosticParticipation) {
            throw new \RuntimeException('First recipient must be a PronosticParticipation instance.');
        }

        $vars = [
            'pronostic_title' => self::escape($pronostic->title),
            'team_1' => self::escape($pronostic->team1),
            'team_2' => self::escape($pronostic->team2),
            'result_team_1_score' => (string) $pronostic->resultTeam1Score,
            'result_team_2_score' => (string) $pronostic->resultTeam2Score,
        ];

        $message = new self(
            Uuid::v4(),
            $participation->adherent->getEmailAddress(),
            $participation->adherent->getFullName(),
            \sprintf('Résultats du pronostic : %s', $pronostic->title),
            $vars,
            self::getRecipientVars($pronostic, $participation),
        );

        foreach ($participations as $participation) {
            $message->addRecipient(
                $participation->adherent->getEmailAddress(),
                $participation->adherent->getFullName(),
                self::getRecipientVars($pronostic, $participation),
            );
        }

        return self::updateSenderInfo($message);
    }

    private static function getRecipientVars(Pronostic $pronostic, PronosticParticipation $participation): array
    {
        return [
            'target_firstname' => self::escape($participation->adherent->getFirstName()),
            'result_status' => $pronostic->isWonBy($participation) ? 'Gagné' : 'Perdu',
            'is_winner' => $pronostic->isWonBy($participation) ? '1' : '0',
            'user_team_1_score' => (string) $participation->team1Score,
            'user_team_2_score' => (string) $participation->team2Score,
        ];
    }
}
