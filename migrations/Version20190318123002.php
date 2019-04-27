<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190318123002 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representatives_register (
          id INT AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          department_id INT DEFAULT NULL, 
          commune_id INT DEFAULT NULL, 
          type_elu VARCHAR(255) DEFAULT NULL, 
          dpt VARCHAR(255) DEFAULT NULL, 
          dpt_nom VARCHAR(255) DEFAULT NULL, 
          nom VARCHAR(255) DEFAULT NULL, 
          prenom VARCHAR(255) DEFAULT NULL, 
          genre VARCHAR(255) DEFAULT NULL, 
          date_naissance DATETIME DEFAULT NULL, 
          code_profession BIGINT DEFAULT NULL, 
          nom_profession LONGTEXT DEFAULT NULL, 
          date_debut_mandat LONGTEXT DEFAULT NULL, 
          nom_fonction VARCHAR(255) DEFAULT NULL, 
          date_debut_fonction DATETIME DEFAULT NULL, 
          nuance_politique VARCHAR(255) DEFAULT NULL, 
          identification_elu BIGINT DEFAULT NULL, 
          nationalite_elu VARCHAR(255) DEFAULT NULL, 
          epci_siren BIGINT DEFAULT NULL, 
          epci_nom VARCHAR(255) DEFAULT NULL, 
          commune_dpt BIGINT DEFAULT NULL, 
          commune_code BIGINT DEFAULT NULL, 
          commune_nom VARCHAR(255) DEFAULT NULL, 
          commune_population BIGINT DEFAULT NULL, 
          canton_code BIGINT DEFAULT NULL, 
          canton_nom VARCHAR(255) DEFAULT NULL, 
          region_code VARCHAR(255) DEFAULT NULL, 
          region_nom VARCHAR(255) DEFAULT NULL, 
          euro_code BIGINT DEFAULT NULL, 
          euro_nom VARCHAR(255) DEFAULT NULL, 
          circo_legis_code BIGINT DEFAULT NULL, 
          circo_legis_nom VARCHAR(255) DEFAULT NULL, 
          infos_supp LONGTEXT DEFAULT NULL, 
          uuid VARCHAR(36) DEFAULT NULL, 
          nb_participation_events INT DEFAULT NULL, 
          adherent_uuid VARCHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', 
          UNIQUE INDEX UNIQ_55314F9525F06C53 (adherent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          elected_representatives_register 
        ADD 
          CONSTRAINT FK_55314F9525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE elected_representatives_register');
    }
}
