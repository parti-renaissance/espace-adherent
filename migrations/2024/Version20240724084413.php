<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240724084413 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE legislative_candidates DROP FOREIGN KEY FK_AE55AF9B23F5C396');
        $this->addSql('ALTER TABLE legislative_candidates DROP FOREIGN KEY FK_AE55AF9BEA9FDD75');
        $this->addSql('DROP TABLE legislative_candidates');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE legislative_candidates (
          id SMALLINT UNSIGNED AUTO_INCREMENT NOT NULL,
          district_zone_id SMALLINT UNSIGNED DEFAULT NULL,
          media_id BIGINT DEFAULT NULL,
          facebook_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          slug VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          gender VARCHAR(6) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          email_address VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          twitter_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          donation_page_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          website_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          district_name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          district_number SMALLINT NOT NULL,
          description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          first_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          last_name VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          display_media TINYINT(1) NOT NULL,
          career VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
          position INT NOT NULL,
          geojson LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
          STATUS VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT \'none\' NOT NULL COLLATE `utf8mb4_unicode_ci`,
          UNIQUE INDEX UNIQ_AE55AF9B989D9B62 (slug),
          INDEX IDX_AE55AF9B23F5C396 (district_zone_id),
          INDEX IDX_AE55AF9BEA9FDD75 (media_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          legislative_candidates
        ADD
          CONSTRAINT FK_AE55AF9B23F5C396 FOREIGN KEY (district_zone_id) REFERENCES legislative_district_zones (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          legislative_candidates
        ADD
          CONSTRAINT FK_AE55AF9BEA9FDD75 FOREIGN KEY (media_id) REFERENCES medias (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
