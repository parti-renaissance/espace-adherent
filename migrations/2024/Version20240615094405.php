<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240615094405 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_matching_history ADD round_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(<<<'SQL'
            UPDATE procuration_v2_matching_history
            SET round_id = (
                SELECT MIN(r.id)
                FROM procuration_v2_rounds AS r
                INNER JOIN procuration_v2_elections AS e
                    ON e.id = r.election_id
                WHERE e.name = :election_name
            )
            SQL,
            [
                'election_name' => 'Élections européennes 2024',
            ]
        );
        $this->addSql('ALTER TABLE procuration_v2_matching_history CHANGE round_id round_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE
          procuration_v2_matching_history
        ADD
          CONSTRAINT FK_4B792213A6005CA0 FOREIGN KEY (round_id) REFERENCES procuration_v2_rounds (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4B792213A6005CA0 ON procuration_v2_matching_history (round_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_matching_history DROP FOREIGN KEY FK_4B792213A6005CA0');
        $this->addSql('DROP INDEX IDX_4B792213A6005CA0 ON procuration_v2_matching_history');
        $this->addSql('ALTER TABLE procuration_v2_matching_history DROP round_id');
    }
}
