<?php

declare(strict_types=1);

namespace App\Adherent\Activity;

class AdherentActivityLabels
{
    public const array SOURCE_TYPES = [
        'hit' => 'Activité in-app',
        'action_history' => "Action de l'utilisateur",
    ];

    public const array HIT_EVENTS = [
        'click' => 'Clic',
        'open' => 'Ouverture',
        'activity_session' => 'Session active',
    ];

    public const array ACTION_HISTORY_EVENTS = [
        'login_success' => 'Connexion réussie',
        'login_failure' => 'Connexion échouée',
        'profile_update' => 'Mise à jour du profil',
        'email_change_request' => "Changement d'email (demande)",
        'email_change_validate' => "Changement d'email (validation)",
        'password_reset_request' => 'Mot de passe oublié (demande)',
        'password_reset_validate' => 'Mot de passe oublié (changement)',
        'role_add' => 'Ajout de rôle',
        'role_remove' => 'Suppression de rôle',
        'live_view' => "Vue d'un live",
        'delegated_access_add' => "Création d'accès délégué",
        'delegated_access_edit' => "Modification d'accès délégué",
        'delegated_access_remove' => "Suppression d'accès délégué",
        'agora_membership_add' => "Membre d'Agora ajouté",
        'agora_membership_remove' => "Membre d'Agora supprimé",
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

    public const array OBJECT_TYPES = [
        'event' => ['article' => 'un', 'label' => 'événement'],
        'publication' => ['article' => 'une', 'label' => 'publication'],
        'news' => ['article' => 'une', 'label' => 'actualité'],
        'alert' => ['article' => 'une', 'label' => 'alerte'],
        'action' => ['article' => 'une', 'label' => 'action'],
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
}
