<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200423060049 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE adherent_certification_histories (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED NOT NULL, 
          administrator_id INT DEFAULT NULL, 
          action VARCHAR(20) NOT NULL, 
          date DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
          INDEX adherent_certification_histories_adherent_id_idx (adherent_id), 
          INDEX adherent_certification_histories_administrator_id_idx (administrator_id), 
          INDEX adherent_certification_histories_date_idx (date), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          adherent_certification_histories 
        ADD 
          CONSTRAINT FK_732EE81A25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          adherent_certification_histories 
        ADD 
          CONSTRAINT FK_732EE81A4B09E92C FOREIGN KEY (administrator_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE adherent_certification_histories');
    }
}
