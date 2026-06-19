<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619172723 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE pronostic_reminder (
              id INT UNSIGNED AUTO_INCREMENT NOT NULL,
              type VARCHAR(255) NOT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              pronostic_id INT UNSIGNED NOT NULL,
              INDEX IDX_65912AC42DD5CFE7 (pronostic_id),
              UNIQUE INDEX uniq_pronostic_reminder (pronostic_id, type),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              pronostic_reminder
            ADD
              CONSTRAINT FK_65912AC42DD5CFE7 FOREIGN KEY (pronostic_id) REFERENCES pronostic (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pronostic_reminder DROP FOREIGN KEY FK_65912AC42DD5CFE7');
        $this->addSql('DROP TABLE pronostic_reminder');
    }
}
