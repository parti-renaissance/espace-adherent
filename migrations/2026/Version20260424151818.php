<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260424151818 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE adherent_activity (
              id INT UNSIGNED AUTO_INCREMENT NOT NULL,
              adherent_id INT UNSIGNED NOT NULL,
              source_type VARCHAR(50) NOT NULL,
              source_id INT UNSIGNED NOT NULL,
              event_type VARCHAR(100) NOT NULL,
              occurred_at DATETIME NOT NULL,
              metadata JSON DEFAULT NULL,
              created_at DATETIME NOT NULL,
              uuid CHAR(36) NOT NULL COMMENT '(DC2Type:uuid)',
              UNIQUE INDEX UNIQ_A6F1D64FD17F50A6 (uuid),
              INDEX IDX_A6F1D64F25F06C53 (adherent_id),
              INDEX IDX_A6F1D64F25F06C5387C03D1B (adherent_id, occurred_at),
              UNIQUE INDEX UNIQ_A6F1D64F8D54D22A953C1C61 (source_type, source_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              adherent_activity
            ADD
              CONSTRAINT FK_A6F1D64F25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_activity
                ADD CONSTRAINT chk_user_activity_history_no_sensitive_event_type
                CHECK (event_type NOT IN ('impersonation_start', 'impersonation_end', 'sensitive_data_access'))
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE adherent_activity
                ADD CONSTRAINT chk_user_activity_history_hit_event_type
                CHECK (source_type != 'hit' OR event_type IN ('open', 'click', 'activity_session'))
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_activity DROP FOREIGN KEY FK_A6F1D64F25F06C53');
        $this->addSql('DROP TABLE adherent_activity');
    }
}
