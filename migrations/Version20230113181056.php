<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230113181056 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP FOREIGN KEY FK_562C7DA379DE69AA');
        $this->addSql('ALTER TABLE municipal_manager_role_association_cities DROP FOREIGN KEY FK_A713D9C2D96891C');
        $this->addSql('DROP TABLE municipal_manager_role_association');
        $this->addSql('DROP TABLE municipal_manager_role_association_cities');
        $this->addSql('DROP INDEX UNIQ_562C7DA379DE69AA ON adherents');
        $this->addSql('ALTER TABLE adherents DROP municipal_manager_role_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE municipal_manager_role_association (
          id INT AUTO_INCREMENT NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
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
        ) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\'');
        $this->addSql('ALTER TABLE
          municipal_manager_role_association_cities
        ADD
          CONSTRAINT FK_A713D9C28BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE
          municipal_manager_role_association_cities
        ADD
          CONSTRAINT FK_A713D9C2D96891C FOREIGN KEY (
            municipal_manager_role_association_id
          ) REFERENCES municipal_manager_role_association (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE adherents ADD municipal_manager_role_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE
          adherents
        ADD
          CONSTRAINT FK_562C7DA379DE69AA FOREIGN KEY (municipal_manager_role_id) REFERENCES municipal_manager_role_association (id) ON UPDATE NO ACTION ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_562C7DA379DE69AA ON adherents (municipal_manager_role_id)');
    }
}
