<?php

declare(strict_types=1);

namespace Tests\App\Adherent\Activity;

use App\Adherent\Activity\AdherentActivityDescriptionBuilder;
use PHPUnit\Framework\TestCase;

class AdherentActivityDescriptionBuilderTest extends TestCase
{
    private AdherentActivityDescriptionBuilder $builder;

    protected function setUp(): void
    {
        $this->builder = new AdherentActivityDescriptionBuilder();
    }

    public function testBuildDelegatedAccessAddWithFullDataReturnsFullSentence(): void
    {
        $description = $this->builder->build('delegated_access_add', [
            'actor_name' => 'Victorio Fortest',
            'role' => 'Responsable de communication',
            'zones' => ['Hauts-de-Seine (92)'],
        ]);

        self::assertSame('Victorio Fortest a ouvert un accès "Responsable de communication" sur Hauts-de-Seine (92)', $description);
    }

    public function testBuildDelegatedAccessAddWithoutActorReturnsNull(): void
    {
        $description = $this->builder->build('delegated_access_add', [
            'role' => 'Correspondant',
            'zones' => ['Paris (75)'],
        ]);

        self::assertNull($description);
    }

    public function testBuildDelegatedAccessAddWithoutRoleAndZonesReturnsActorOnly(): void
    {
        $description = $this->builder->build('delegated_access_add', [
            'actor_name' => 'Alice Martin',
        ]);

        self::assertSame('Alice Martin a ouvert un accès', $description);
    }

    public function testBuildProfileUpdateSingleField(): void
    {
        $description = $this->builder->build('profile_update', [
            'modified_field_labels' => ['Date de naissance'],
        ]);

        self::assertSame('Date de naissance modifié', $description);
    }

    public function testBuildProfileUpdateTwoFields(): void
    {
        $description = $this->builder->build('profile_update', [
            'modified_field_labels' => ['Prénom', 'Nom'],
        ]);

        self::assertSame('Prénom et Nom modifiés', $description);
    }

    public function testBuildProfileUpdateThreeOrMoreFields(): void
    {
        $description = $this->builder->build('profile_update', [
            'modified_field_labels' => ['Prénom', 'Nom', 'Email', 'Téléphone'],
        ]);

        self::assertSame('Prénom et 3 autres modifiés', $description);
    }

    public function testBuildProfileUpdateNoLabelsReturnsNull(): void
    {
        self::assertNull($this->builder->build('profile_update', ['modified_field_labels' => []]));
        self::assertNull($this->builder->build('profile_update', []));
    }

    public function testBuildClickWithButtonAndObject(): void
    {
        $description = $this->builder->build('click', [
            'button_name' => 'cta_register',
            'object_type' => 'event',
            'object_name' => 'Nuit de la Nouvelle République',
        ]);

        self::assertSame('A cliqué sur "S\'inscrire" depuis un événement "Nuit de la Nouvelle République"', $description);
    }

    public function testBuildClickWithButtonOnlyReturnsButtonOnly(): void
    {
        $description = $this->builder->build('click', [
            'button_name' => 'cta_share',
        ]);

        self::assertSame('A cliqué sur "Partager"', $description);
    }

    public function testBuildOpenWithObjectAndSourceReturnsCombined(): void
    {
        $description = $this->builder->build('open', [
            'object_type' => 'event',
            'object_name' => 'Convention nationale',
            'source' => 'page_events',
        ]);

        self::assertSame('A ouvert un événement "Convention nationale" (la page Événements)', $description);
    }

    public function testBuildOpenWithObjectOnly(): void
    {
        $description = $this->builder->build('open', [
            'object_type' => 'publication',
            'object_name' => 'Édito du président',
        ]);

        self::assertSame('A ouvert une publication "Édito du président"', $description);
    }

    public function testBuildActivitySessionWithSourceReturnsSentence(): void
    {
        $description = $this->builder->build('activity_session', [
            'source' => 'page_timeline',
        ]);

        self::assertSame('Était actif sur la timeline', $description);
    }

    public function testBuildActivitySessionWithoutSourceReturnsNull(): void
    {
        self::assertNull($this->builder->build('activity_session', []));
    }

    public function testBuildLoginSuccessReturnsNull(): void
    {
        self::assertNull($this->builder->build('login_success', ['ip' => '127.0.0.1']));
    }

    public function testBuildUnknownEventTypeReturnsNull(): void
    {
        self::assertNull($this->builder->build('something_unknown', []));
    }

    public function testBuildAcceptsNullMetadata(): void
    {
        self::assertNull($this->builder->build('click', null));
        self::assertNull($this->builder->build('login_success', null));
    }
}
