<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250613144613 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  into_image_id INT UNSIGNED DEFAULT NULL,
                ADD
                  created_by_administrator_id INT DEFAULT NULL,
                ADD
                  updated_by_administrator_id INT DEFAULT NULL,
                ADD
                  type VARCHAR(255) DEFAULT 'default' NOT NULL,
                ADD
                  transport_configuration JSON DEFAULT NULL,
                DROP
                  into_image_path
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  CONSTRAINT FK_AD037664DC0A230D FOREIGN KEY (into_image_id) REFERENCES uploadable_file (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  CONSTRAINT FK_AD0376649DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  CONSTRAINT FK_AD037664CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql(<<<'SQL'
                CREATE UNIQUE INDEX UNIQ_AD037664DC0A230D ON national_event (into_image_id)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_AD0376649DF5350C ON national_event (created_by_administrator_id)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_AD037664CF1918FF ON national_event (updated_by_administrator_id)
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event_inscription
                ADD
                  is_jam TINYINT(1) DEFAULT 0 NOT NULL,
                ADD
                  visit_day VARCHAR(255) DEFAULT NULL,
                ADD
                  transport VARCHAR(255) DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event DROP FOREIGN KEY FK_AD037664DC0A230D
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event DROP FOREIGN KEY FK_AD0376649DF5350C
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event DROP FOREIGN KEY FK_AD037664CF1918FF
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX UNIQ_AD037664DC0A230D ON national_event
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_AD0376649DF5350C ON national_event
            SQL);
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_AD037664CF1918FF ON national_event
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  national_event
                ADD
                  into_image_path VARCHAR(255) DEFAULT NULL,
                DROP
                  into_image_id,
                DROP
                  created_by_administrator_id,
                DROP
                  updated_by_administrator_id,
                DROP
                  type,
                DROP
                  transport_configuration
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE national_event_inscription DROP is_jam, DROP visit_day, DROP transport
            SQL);
    }
}
