<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190409152550 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE referent_space_access_information (
          id INT AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED NOT NULL, 
          previous_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
          last_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', 
          UNIQUE INDEX UNIQ_CD8FDF4825F06C53 (adherent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          referent_space_access_information 
        ADD 
          CONSTRAINT FK_CD8FDF4825F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE referent_space_access_information');
    }
}
