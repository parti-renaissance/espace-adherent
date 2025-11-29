<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250723100955 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE TABLE event_inscription_zone (
                  event_inscription_id INT UNSIGNED NOT NULL,
                  zone_id INT UNSIGNED NOT NULL,
                  INDEX IDX_A74D587C82E8EFE0 (event_inscription_id),
                  INDEX IDX_A74D587C9F2C3FAB (zone_id),
                  PRIMARY KEY(event_inscription_id, zone_id)
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  event_inscription_zone
                ADD
                  CONSTRAINT FK_A74D587C82E8EFE0 FOREIGN KEY (event_inscription_id) REFERENCES national_event_inscription (id) ON DELETE CASCADE
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  event_inscription_zone
                ADD
                  CONSTRAINT FK_A74D587C9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE event_inscription_zone DROP FOREIGN KEY FK_A74D587C82E8EFE0
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE event_inscription_zone DROP FOREIGN KEY FK_A74D587C9F2C3FAB
            SQL);
        $this->addSql(<<<'SQL'
                DROP TABLE event_inscription_zone
            SQL);
    }
}
