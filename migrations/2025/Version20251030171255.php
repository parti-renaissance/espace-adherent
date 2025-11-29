<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251030171255 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE moodle_user (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  adherent_id INT UNSIGNED NOT NULL,
                  moodle_id INT UNSIGNED NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  UNIQUE INDEX UNIQ_5EB3C2D25F06C53 (adherent_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                CREATE TABLE moodle_user_job (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  user_id INT UNSIGNED NOT NULL,
                  moodle_id INT UNSIGNED NOT NULL,
                  department VARCHAR(255) NOT NULL,
                  position VARCHAR(255) NOT NULL,
                  job_key VARCHAR(255) NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  INDEX IDX_47A4CBDA76ED395 (user_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  moodle_user
                ADD
                  CONSTRAINT FK_5EB3C2D25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  moodle_user_job
                ADD
                  CONSTRAINT FK_47A4CBDA76ED395 FOREIGN KEY (user_id) REFERENCES moodle_user (id) ON DELETE CASCADE
            SQL);
        $this->addSql('ALTER TABLE oauth_clients ADD session_enabled TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE moodle_user DROP FOREIGN KEY FK_5EB3C2D25F06C53');
        $this->addSql('ALTER TABLE moodle_user_job DROP FOREIGN KEY FK_47A4CBDA76ED395');
        $this->addSql('DROP TABLE moodle_user');
        $this->addSql('DROP TABLE moodle_user_job');
        $this->addSql('ALTER TABLE oauth_clients DROP session_enabled');
    }
}
