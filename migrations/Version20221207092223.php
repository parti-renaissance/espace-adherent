<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221207092223 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_formation (
          id BIGINT AUTO_INCREMENT NOT NULL,
          file_id INT UNSIGNED DEFAULT NULL,
          title VARCHAR(255) NOT NULL,
          description LONGTEXT DEFAULT NULL,
          visible TINYINT(1) DEFAULT \'0\' NOT NULL,
          downloads_count SMALLINT UNSIGNED NOT NULL,
          position SMALLINT UNSIGNED DEFAULT 0 NOT NULL,
          UNIQUE INDEX UNIQ_2D97408B2B36786B (title),
          UNIQUE INDEX UNIQ_2D97408B93CB796C (file_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE adherent_formation_file (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          title VARCHAR(255) NOT NULL,
          slug VARCHAR(255) NOT NULL,
          path VARCHAR(255) NOT NULL,
          extension VARCHAR(255) NOT NULL,
          UNIQUE INDEX adherent_formation_file_slug_extension (slug, extension),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          adherent_formation
        ADD
          CONSTRAINT FK_2D97408B93CB796C FOREIGN KEY (file_id) REFERENCES adherent_formation_file (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_formation DROP FOREIGN KEY FK_2D97408B93CB796C');
        $this->addSql('DROP TABLE adherent_formation');
        $this->addSql('DROP TABLE adherent_formation_file');
    }
}
