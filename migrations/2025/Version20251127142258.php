<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251127142258 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_provisional_supervisor DROP FOREIGN KEY FK_E394C3D425F06C53');
        $this->addSql('ALTER TABLE committee_provisional_supervisor DROP FOREIGN KEY FK_E394C3D4ED1A100B');
        $this->addSql('DROP TABLE committee_provisional_supervisor');
        $this->addSql('ALTER TABLE adherent_message_filters DROP include_committee_provisional_supervisors');
        $this->addSql('ALTER TABLE projection_managed_users DROP is_committee_provisional_supervisor');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE committee_provisional_supervisor (
                  id INT UNSIGNED AUTO_INCREMENT NOT NULL,
                  adherent_id INT UNSIGNED DEFAULT NULL,
                  committee_id INT UNSIGNED NOT NULL,
                  created_at DATETIME NOT NULL,
                  updated_at DATETIME NOT NULL,
                  INDEX IDX_E394C3D425F06C53 (adherent_id),
                  INDEX IDX_E394C3D4ED1A100B (committee_id),
                  PRIMARY KEY(id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_provisional_supervisor
                ADD
                  CONSTRAINT FK_E394C3D425F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON
                UPDATE
                  NO ACTION ON DELETE NO ACTION
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  committee_provisional_supervisor
                ADD
                  CONSTRAINT FK_E394C3D4ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_filters
                ADD
                  include_committee_provisional_supervisors TINYINT(1) DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  projection_managed_users
                ADD
                  is_committee_provisional_supervisor TINYINT(1) NOT NULL
            SQL);
    }
}
