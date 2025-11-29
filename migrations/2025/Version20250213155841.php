<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250213155841 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE uploadable_file (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          file_name VARCHAR(255) DEFAULT NULL,
          file_original_name VARCHAR(255) DEFAULT NULL,
          file_mime_type VARCHAR(255) DEFAULT NULL,
          file_size INT DEFAULT NULL,
          file_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\',
          UNIQUE INDEX UNIQ_55DF92E4D17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          national_event
        ADD
          og_image_id INT UNSIGNED DEFAULT NULL,
        ADD
          og_title VARCHAR(255) DEFAULT NULL,
        ADD
          og_description VARCHAR(255) DEFAULT NULL,
        ADD
          alert_title VARCHAR(255) DEFAULT NULL,
        ADD
          alert_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE
          national_event
        ADD
          CONSTRAINT FK_AD0376646EFCB8B8 FOREIGN KEY (og_image_id) REFERENCES uploadable_file (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD0376646EFCB8B8 ON national_event (og_image_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE national_event DROP FOREIGN KEY FK_AD0376646EFCB8B8');
        $this->addSql('DROP TABLE uploadable_file');
        $this->addSql('DROP INDEX UNIQ_AD0376646EFCB8B8 ON national_event');
        $this->addSql('ALTER TABLE
          national_event
        DROP
          og_image_id,
        DROP
          og_title,
        DROP
          og_description,
        DROP
          alert_title,
        DROP
          alert_description');
    }
}
