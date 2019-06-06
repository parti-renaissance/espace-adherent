<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190606140322 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE municipal_chief_areas (
          id INT AUTO_INCREMENT NOT NULL, 
          codes LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE adherents ADD municipal_chief_managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA39E544A1 FOREIGN KEY (
            municipal_chief_managed_area_id
          ) REFERENCES municipal_chief_areas (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3CC72679B ON adherents (municipal_chief_managed_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA39E544A1');
        $this->addSql('DROP TABLE municipal_chief_areas');
        $this->addSql('DROP INDEX UNIQ_562C7DA3CC72679B ON adherents');
        $this->addSql('ALTER TABLE adherents DROP municipal_chief_managed_area_id');
    }
}
