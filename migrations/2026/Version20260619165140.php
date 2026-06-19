<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260619165140 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE pronostic (
                  title VARCHAR(255) NOT NULL,
                  team1 VARCHAR(255) NOT NULL,
                  team2 VARCHAR(255) NOT NULL,
                  gabriel_team1_score SMALLINT UNSIGNED NOT NULL,
                  gabriel_team2_score SMALLINT UNSIGNED NOT NULL,
                  begin_at DATETIME NOT NULL,
                  match_at DATETIME NOT NULL,
                  result_team1_score SMALLINT UNSIGNED DEFAULT NULL,
                  result_team2_score SMALLINT UNSIGNED DEFAULT NULL,
                  result_published_at DATETIME DEFAULT NULL,
                  displayed TINYINT DEFAULT 0 NOT NULL,
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  uuid CHAR(36) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  created_by_administrator_id INT DEFAULT NULL,
                  updated_by_administrator_id INT DEFAULT NULL,
                  UNIQUE INDEX UNIQ_E64BDCDED17F50A6 (uuid),
                  INDEX IDX_E64BDCDE9DF5350C (created_by_administrator_id),
                  INDEX IDX_E64BDCDECF1918FF (updated_by_administrator_id),
                  INDEX IDX_PRONOSTIC_PERIOD (begin_at, match_at),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE pronostic_participation (
                  team1_score SMALLINT UNSIGNED NOT NULL,
                  team2_score SMALLINT UNSIGNED NOT NULL,
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  uuid CHAR(36) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  pronostic_id INT UNSIGNED NOT NULL,
                  adherent_id INT UNSIGNED NOT NULL,
                  UNIQUE INDEX UNIQ_D6CD0219D17F50A6 (uuid),
                  INDEX IDX_D6CD02192DD5CFE7 (pronostic_id),
                  INDEX IDX_D6CD021925F06C53 (adherent_id),
                  UNIQUE INDEX uniq_pronostic_participation (pronostic_id, adherent_id),
                  PRIMARY KEY (id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pronostic
                ADD
                  CONSTRAINT FK_E64BDCDE9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pronostic
                ADD
                  CONSTRAINT FK_E64BDCDECF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pronostic_participation
                ADD
                  CONSTRAINT FK_D6CD02192DD5CFE7 FOREIGN KEY (pronostic_id) REFERENCES pronostic (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  pronostic_participation
                ADD
                  CONSTRAINT FK_D6CD021925F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pronostic DROP FOREIGN KEY FK_E64BDCDE9DF5350C');
        $this->addSql('ALTER TABLE pronostic DROP FOREIGN KEY FK_E64BDCDECF1918FF');
        $this->addSql('ALTER TABLE pronostic_participation DROP FOREIGN KEY FK_D6CD02192DD5CFE7');
        $this->addSql('ALTER TABLE pronostic_participation DROP FOREIGN KEY FK_D6CD021925F06C53');
        $this->addSql('DROP TABLE pronostic');
        $this->addSql('DROP TABLE pronostic_participation');
    }
}
