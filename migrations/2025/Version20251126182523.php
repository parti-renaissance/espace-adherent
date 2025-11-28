<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251126182523 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE my_team_delegate_access_committee DROP FOREIGN KEY FK_C52A163FED1A100B');
        $this->addSql('ALTER TABLE my_team_delegate_access_committee DROP FOREIGN KEY FK_C52A163FFD98FA7A');
        $this->addSql('DROP TABLE my_team_delegate_access_committee');
        $this->addSql('ALTER TABLE my_team_delegated_access DROP restricted_cities');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE my_team_delegate_access_committee (
                  delegated_access_id INT UNSIGNED NOT NULL,
                  committee_id INT UNSIGNED NOT NULL,
                  INDEX IDX_C52A163FED1A100B (committee_id),
                  INDEX IDX_C52A163FFD98FA7A (delegated_access_id),
                  PRIMARY KEY(
                    delegated_access_id, committee_id
                  )
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = ''
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_delegate_access_committee
                ADD
                  CONSTRAINT FK_C52A163FED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_delegate_access_committee
                ADD
                  CONSTRAINT FK_C52A163FFD98FA7A FOREIGN KEY (delegated_access_id) REFERENCES my_team_delegated_access (id) ON
                UPDATE
                  NO ACTION ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  my_team_delegated_access
                ADD
                  restricted_cities LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'
            SQL);
    }
}
