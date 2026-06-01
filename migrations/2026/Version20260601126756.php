<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260601126756 extends AbstractMigration
{
    private const SOURCES = [
        'em' => [
            'label' => 'EM',
            'enabled' => false,
            'tags' => ['compte_em'],
        ],
        'avecvous' => [
            'label' => 'AvecVous',
            'enabled' => false,
            'tags' => ['compte_avecvous_jemengage'],
        ],
        'besoindeurope' => [
            'label' => 'Besoin d\'Europe',
            'enabled' => false,
            'tags' => ['besoin_d_europe'],
        ],
        'ensemble2024' => [
            'label' => 'Ensemble 2024',
            'enabled' => false,
            'tags' => ['ensemble2024'],
        ],
        'renaissance' => [
            'label' => 'Renaissance',
            'enabled' => true,
            'tags' => ['adhesion_incomplete', 'autre_parti'],
        ],
    ];

    public function up(Schema $schema): void
    {
        foreach (self::SOURCES as $code => $data) {
            $this->addSql(
                'INSERT IGNORE INTO signup_source (uuid, code, label, enabled, created_at, updated_at) VALUES (UUID(), :code, :label, :enabled, NOW(), NOW())',
                ['code' => $code, 'label' => $data['label'], 'enabled' => $data['enabled'] ? 1 : 0]
            );
        }

        foreach (self::SOURCES as $code => $data) {
            $conditions = [];
            $params = ['code' => $code];

            foreach ($data['tags'] as $i => $suffix) {
                $conditions[] = 'a.tags LIKE :pattern'.$i;
                $params['pattern'.$i] = '%sympathisant:'.$suffix.'%';
            }

            $this->addSql(
                \sprintf(
                    <<<'SQL'
                        INSERT IGNORE INTO adherent_signup_source (adherent_id, source, captured_at)
                        SELECT a.id, :code, a.registered_at
                        FROM adherents a
                        WHERE %s
                        SQL,
                    implode(' OR ', $conditions)
                ),
                $params
            );
        }
    }

    public function down(Schema $schema): void
    {
    }
}
