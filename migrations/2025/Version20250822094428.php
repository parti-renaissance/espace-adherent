<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250822094428 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_hit (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          referent_id INT UNSIGNED DEFAULT NULL,
          app_session_id INT UNSIGNED DEFAULT NULL,
          event_type VARCHAR(255) NOT NULL,
          referent_code VARCHAR(255) DEFAULT NULL,
          activity_session_uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          open_type VARCHAR(255) DEFAULT NULL,
          object_type VARCHAR(255) DEFAULT NULL,
          object_id VARCHAR(255) DEFAULT NULL,
          source VARCHAR(255) DEFAULT NULL,
          button_name VARCHAR(255) DEFAULT NULL,
          target_url LONGTEXT DEFAULT NULL,
          user_agent LONGTEXT DEFAULT NULL,
          app_system VARCHAR(255) DEFAULT NULL,
          app_version VARCHAR(255) NOT NULL,
          app_date DATETIME NOT NULL,
          raw JSON NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          utm_source VARCHAR(255) DEFAULT NULL,
          utm_campaign VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_74A09586D17F50A6 (uuid),
          INDEX IDX_74A0958625F06C53 (adherent_id),
          INDEX IDX_74A0958635E47E35 (referent_id),
          INDEX IDX_74A09586372447A3 (app_session_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          app_hit
        ADD
          CONSTRAINT FK_74A0958625F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          app_hit
        ADD
          CONSTRAINT FK_74A0958635E47E35 FOREIGN KEY (referent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          app_hit
        ADD
          CONSTRAINT FK_74A09586372447A3 FOREIGN KEY (app_session_id) REFERENCES app_session (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_hit DROP FOREIGN KEY FK_74A0958625F06C53');
        $this->addSql('ALTER TABLE app_hit DROP FOREIGN KEY FK_74A0958635E47E35');
        $this->addSql('ALTER TABLE app_hit DROP FOREIGN KEY FK_74A09586372447A3');
        $this->addSql('DROP TABLE app_hit');
    }
}
