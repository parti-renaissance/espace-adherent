<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220218125939 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE procuration_proxy_zone (
          procuration_proxy_id INT UNSIGNED NOT NULL,
          zone_id INT UNSIGNED NOT NULL,
          INDEX IDX_5AE81518E15E419B (procuration_proxy_id),
          INDEX IDX_5AE815189F2C3FAB (zone_id),
          PRIMARY KEY(procuration_proxy_id, zone_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          procuration_proxy_zone
        ADD
          CONSTRAINT FK_5AE81518E15E419B FOREIGN KEY (procuration_proxy_id) REFERENCES procuration_proxies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_proxy_zone
        ADD
          CONSTRAINT FK_5AE815189F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE
          procuration_proxies
        CHANGE
          other_vote_cities backup_other_vote_cities VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE procuration_proxy_zone');
        $this->addSql('ALTER TABLE
          procuration_proxies
        CHANGE
          backup_other_vote_cities other_vote_cities VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`');
    }
}
