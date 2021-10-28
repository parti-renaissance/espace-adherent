<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210818093317 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audience_zone (
          audience_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_A719804F848CC616 (audience_id),
          INDEX IDX_A719804F9F2C3FAB (zone_id),
          PRIMARY KEY(audience_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          audience_zone
        ADD
          CONSTRAINT FK_A719804F848CC616 FOREIGN KEY (audience_id) REFERENCES audience (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          audience_zone
        ADD
          CONSTRAINT FK_A719804F9F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          audience
        ADD
          scope VARCHAR(255) DEFAULT NULL,
        ADD
          created_at DATETIME NOT NULL,
        ADD
          updated_at DATETIME NOT NULL,
        DROP
          type');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audience_zone');
        $this->addSql('ALTER TABLE
          audience
        ADD
          type VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
        DROP
          scope,
        DROP
          created_at,
        DROP
          updated_at');
    }
}
