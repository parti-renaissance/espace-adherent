<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200221103832 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE municipal_manager_role_association (
          id INT AUTO_INCREMENT NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE municipal_manager_role_association_cities (
          municipal_manager_role_association_id INT NOT NULL, 
          city_id INT UNSIGNED NOT NULL, 
          INDEX IDX_A713D9C2D96891C (
            municipal_manager_role_association_id
          ), 
          UNIQUE INDEX UNIQ_A713D9C28BAC62AF (city_id), 
          PRIMARY KEY(
            municipal_manager_role_association_id, 
            city_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cities (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          name VARCHAR(100) NOT NULL, 
          insee_code VARCHAR(10) NOT NULL, 
          postal_code VARCHAR(10) NOT NULL, 
          country VARCHAR(2) NOT NULL, 
          UNIQUE INDEX UNIQ_D95DB16B15A3C1BC (insee_code), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          municipal_manager_role_association_cities 
        ADD 
          CONSTRAINT FK_A713D9C2D96891C FOREIGN KEY (
            municipal_manager_role_association_id
          ) REFERENCES municipal_manager_role_association (id)');
        $this->addSql('ALTER TABLE 
          municipal_manager_role_association_cities 
        ADD 
          CONSTRAINT FK_A713D9C28BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE adherents ADD municipal_manager_role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          CONSTRAINT FK_562C7DA379DE69AA FOREIGN KEY (municipal_manager_role_id) REFERENCES municipal_manager_role_association (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA379DE69AA ON adherents (municipal_manager_role_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA379DE69AA');
        $this->addSql('ALTER TABLE municipal_manager_role_association_cities DROP FOREIGN KEY FK_A713D9C2D96891C');
        $this->addSql('ALTER TABLE municipal_manager_role_association_cities DROP FOREIGN KEY FK_A713D9C28BAC62AF');
        $this->addSql('DROP TABLE municipal_manager_role_association');
        $this->addSql('DROP TABLE municipal_manager_role_association_cities');
        $this->addSql('DROP TABLE cities');
        $this->addSql('DROP INDEX UNIQ_562C7DA379DE69AA ON adherents');
        $this->addSql('ALTER TABLE adherents DROP municipal_manager_role_id');
    }
}
