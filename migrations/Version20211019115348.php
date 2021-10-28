<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211019115348 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_campaign (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          survey_id INT UNSIGNED DEFAULT NULL,
          administrator_id INT DEFAULT NULL,
          title VARCHAR(255) NOT NULL,
          brief LONGTEXT DEFAULT NULL,
          goal INT NOT NULL,
          begin_at DATETIME DEFAULT NULL,
          finish_at DATETIME DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_EF50C8E8B3FE509D (survey_id),
          INDEX IDX_EF50C8E84B09E92C (administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_15CB2432B3FE509D FOREIGN KEY (survey_id) REFERENCES jecoute_survey (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          pap_campaign
        ADD
          CONSTRAINT FK_15CB24324B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE pap_campaign');
    }
}
