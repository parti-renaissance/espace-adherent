<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Activity\Api;

use ApiPlatform\Metadata\Get;
use App\Adherent\Activity\Api\AdherentActivityFilterProvider;
use App\Entity\Adherent\Activity\AdherentActivityFilter;
use PHPUnit\Framework\TestCase;

class AdherentActivityFilterProviderTest extends TestCase
{
    public function testProvideReturnsFilterDto(): void
    {
        $result = new AdherentActivityFilterProvider()->provide(new Get(uriTemplate: '/adherent-activity-filters'));

        self::assertInstanceOf(AdherentActivityFilter::class, $result);
    }

    public function testProvideExposesExactHitEvents(): void
    {
        $result = new AdherentActivityFilterProvider()->provide(new Get());

        self::assertSame([
            ['value' => 'click', 'label' => 'Clic'],
            ['value' => 'open', 'label' => 'Ouverture'],
            ['value' => 'activity_session', 'label' => 'Session active'],
        ], $result->eventTypes['hit']);
    }

    public function testProvideExposesExactActionHistoryEvents(): void
    {
        $result = new AdherentActivityFilterProvider()->provide(new Get());

        self::assertSame([
            ['value' => 'login_success', 'label' => 'Connexion réussie'],
            ['value' => 'login_failure', 'label' => 'Connexion échouée'],
            ['value' => 'profile_update', 'label' => 'Mise à jour du profil'],
            ['value' => 'email_change_request', 'label' => "Changement d'email (demande)"],
            ['value' => 'email_change_validate', 'label' => "Changement d'email (validation)"],
            ['value' => 'password_reset_request', 'label' => 'Mot de passe oublié (demande)'],
            ['value' => 'password_reset_validate', 'label' => 'Mot de passe oublié (changement)'],
            ['value' => 'role_add', 'label' => 'Ajout de rôle'],
            ['value' => 'role_remove', 'label' => 'Suppression de rôle'],
            ['value' => 'live_view', 'label' => "Vue d'un live"],
            ['value' => 'delegated_access_add', 'label' => "Création d'accès délégué"],
            ['value' => 'delegated_access_edit', 'label' => "Modification d'accès délégué"],
            ['value' => 'delegated_access_remove', 'label' => "Suppression d'accès délégué"],
            ['value' => 'agora_membership_add', 'label' => "Membre d'Agora ajouté"],
            ['value' => 'agora_membership_remove', 'label' => "Membre d'Agora supprimé"],
            ['value' => 'agora_president_add', 'label' => "Président d'Agora ajouté"],
            ['value' => 'agora_president_remove', 'label' => "Président d'Agora supprimé"],
            ['value' => 'agora_general_secretary_add', 'label' => "Secrétaire général d'Agora ajouté"],
            ['value' => 'agora_general_secretary_remove', 'label' => "Secrétaire général d'Agora supprimé"],
            ['value' => 'membership_anniversary_reminded', 'label' => "Rappel anniversaire d'adhésion"],
            ['value' => 'committee_create', 'label' => 'Création de comité'],
            ['value' => 'committee_update', 'label' => 'Modification de comité'],
            ['value' => 'committee_delete', 'label' => 'Suppression de comité'],
        ], $result->eventTypes['action_history']);
    }

    public function testProvideOnlyExposesHitAndActionHistoryGroups(): void
    {
        $result = new AdherentActivityFilterProvider()->provide(new Get());

        self::assertSame(['hit', 'action_history'], array_keys($result->eventTypes));
    }
}
