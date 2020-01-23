<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200123111724 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE consular_managed_area (
          id INT AUTO_INCREMENT NOT NULL, 
          consular_district_id INT UNSIGNED DEFAULT NULL, 
          INDEX IDX_7937A51292CA96FD (consular_district_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consular_districts (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          countries LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', 
          cities LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', 
          code VARCHAR(6) NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          consular_managed_area 
        ADD 
          CONSTRAINT FK_7937A51292CA96FD FOREIGN KEY (consular_district_id) REFERENCES consular_districts (id)');
        $this->addSql('ALTER TABLE adherents ADD consular_managed_area_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA3122E5FF4 FOREIGN KEY (consular_managed_area_id) REFERENCES consular_managed_area (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA3122E5FF4 ON adherents (consular_managed_area_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA3122E5FF4');
        $this->addSql('ALTER TABLE consular_managed_area DROP FOREIGN KEY FK_7937A51292CA96FD');
        $this->addSql('DROP TABLE consular_managed_area');
        $this->addSql('DROP TABLE consular_districts');
        $this->addSql('DROP INDEX UNIQ_562C7DA3122E5FF4 ON adherents');
        $this->addSql('ALTER TABLE adherents DROP consular_managed_area_id');
    }
}
