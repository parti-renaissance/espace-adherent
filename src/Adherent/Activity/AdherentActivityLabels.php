<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

use App\History\UserActionHistoryTypeEnum;
use App\JeMengage\Hit\EventTypeEnum;
use App\JeMengage\Hit\TargetTypeEnum;

class AdherentActivityLabels
{
    public const array SOURCE_TYPES = [
        SourceTypeEnum::Hit->value => 'Activité in-app',
        SourceTypeEnum::ActionHistory->value => "Action de l'utilisateur",
    ];

    public const array OBJECT_TYPES = [
        TargetTypeEnum::Event->value => ['article' => 'un', 'label' => 'événement'],
        TargetTypeEnum::Publication->value => ['article' => 'une', 'label' => 'publication'],
        TargetTypeEnum::News->value => ['article' => 'une', 'label' => 'actualité'],
        TargetTypeEnum::Alert->value => ['article' => 'une', 'label' => 'alerte'],
        TargetTypeEnum::Action->value => ['article' => 'une', 'label' => 'action'],
    ];

    public const array HIT_EVENTS = [
        EventTypeEnum::Click->value => 'Clic',
        EventTypeEnum::Open->value => 'Ouverture',
        EventTypeEnum::ActivitySession->value => 'Session active',
    ];

    public const array ACTION_HISTORY_EVENTS = [
        UserActionHistoryTypeEnum::LOGIN_SUCCESS->value => 'Connexion réussie',
        UserActionHistoryTypeEnum::LOGIN_FAILURE->value => 'Connexion échouée',
        UserActionHistoryTypeEnum::PROFILE_UPDATE->value => 'Mise à jour du profil',
        UserActionHistoryTypeEnum::EMAIL_CHANGE_REQUEST->value => "Changement d'email (demande)",
        UserActionHistoryTypeEnum::EMAIL_CHANGE_VALIDATE->value => "Changement d'email (validation)",
        UserActionHistoryTypeEnum::PASSWORD_RESET_REQUEST->value => 'Mot de passe oublié (demande)',
        UserActionHistoryTypeEnum::PASSWORD_RESET_VALIDATE->value => 'Mot de passe oublié (changement)',
        UserActionHistoryTypeEnum::ROLE_ADD->value => 'Ajout de rôle',
        UserActionHistoryTypeEnum::ROLE_REMOVE->value => 'Suppression de rôle',
        UserActionHistoryTypeEnum::LIVE_VIEW->value => "Vue d'un live",
        UserActionHistoryTypeEnum::DELEGATED_ACCESS_ADD->value => "Création d'accès délégué",
        UserActionHistoryTypeEnum::DELEGATED_ACCESS_EDIT->value => "Modification d'accès délégué",
        UserActionHistoryTypeEnum::DELEGATED_ACCESS_REMOVE->value => "Suppression d'accès délégué",
        UserActionHistoryTypeEnum::AGORA_MEMBERSHIP_ADD->value => "Membre d'Agora ajouté",
        UserActionHistoryTypeEnum::AGORA_MEMBERSHIP_REMOVE->value => "Membre d'Agora supprimé",
        UserActionHistoryTypeEnum::AGORA_PRESIDENT_ADD->value => "Président d'Agora ajouté",
        UserActionHistoryTypeEnum::AGORA_PRESIDENT_REMOVE->value => "Président d'Agora supprimé",
        UserActionHistoryTypeEnum::AGORA_GENERAL_SECRETARY_ADD->value => "Secrétaire général d'Agora ajouté",
        UserActionHistoryTypeEnum::AGORA_GENERAL_SECRETARY_REMOVE->value => "Secrétaire général d'Agora supprimé",
        UserActionHistoryTypeEnum::MEMBERSHIP_ANNIVERSARY_REMINDED->value => "Rappel anniversaire d'adhésion",
        UserActionHistoryTypeEnum::COMMITTEE_CREATE->value => 'Création de comité',
        UserActionHistoryTypeEnum::COMMITTEE_UPDATE->value => 'Modification de comité',
        UserActionHistoryTypeEnum::COMMITTEE_DELETE->value => 'Suppression de comité',
    ];

    public const array EVENT_TYPES = self::HIT_EVENTS + self::ACTION_HISTORY_EVENTS;

    public const array BUTTON_NAMES = [
        'cta_share' => 'Partager',
        'cta_subscribe' => "S'inscrire",
        'cta_register' => "S'inscrire",
        'cta_remind_me' => 'Me rappeler',
    ];

    public const array METADATA_SOURCES = [
        'direct_link' => 'lien direct',
        'push_notification' => 'notification push',
        'reload' => 'rechargement',
        'page_events' => 'la page Événements',
        'page_timeline' => 'la timeline',
        'page_publication_edition' => 'la page de publication',
    ];

    /** @return list<array{value: string, label: string}> */
    public static function asOptions(array $labels): array
    {
        $options = [];
        foreach ($labels as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }

        return $options;
    }

    /** @return list<string> */
    public static function actionHistoryKeys(): array
    {
        return array_keys(self::ACTION_HISTORY_EVENTS);
    }

    /** @return list<string> */
    public static function hitEventKeys(): array
    {
        return array_keys(self::HIT_EVENTS);
    }
}
