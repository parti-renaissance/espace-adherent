<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20191004164517 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_charter (
          id SMALLINT AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          accepted_at DATETIME NOT NULL, 
          dtype VARCHAR(255) NOT NULL, 
          INDEX IDX_D6F94F2B25F06C53 (adherent_id), 
          UNIQUE INDEX UNIQ_D6F94F2B25F06C5370AAEA5 (adherent_id, dtype), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          adherent_charter 
        ADD 
          CONSTRAINT FK_D6F94F2B25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE adherents DROP chart_accepted');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_charter');
        $this->addSql('ALTER TABLE adherents ADD chart_accepted TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
