<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200512154124 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE elected_representatives_register');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE elected_representatives_register (
          id INT AUTO_INCREMENT NOT NULL, 
          adherent_id INT UNSIGNED DEFAULT NULL, 
          department_id INT DEFAULT NULL, 
          commune_id INT DEFAULT NULL, 
          type_elu VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          dpt VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          dpt_nom VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          nom VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          prenom VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          genre VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          date_naissance DATETIME DEFAULT NULL, 
          code_profession BIGINT DEFAULT NULL, 
          nom_profession LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, 
          date_debut_mandat LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, 
          nom_fonction VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          date_debut_fonction DATETIME DEFAULT NULL, 
          nuance_politique VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          identification_elu BIGINT DEFAULT NULL, 
          nationalite_elu VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          epci_siren BIGINT DEFAULT NULL, 
          epci_nom VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          commune_dpt BIGINT DEFAULT NULL, 
          commune_code BIGINT DEFAULT NULL, 
          commune_nom VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          commune_population BIGINT DEFAULT NULL, 
          canton_code BIGINT DEFAULT NULL, 
          canton_nom VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          region_code VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          region_nom VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          euro_code BIGINT DEFAULT NULL, 
          euro_nom VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          circo_legis_code BIGINT DEFAULT NULL, 
          circo_legis_nom VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, 
          infos_supp LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, 
          uuid VARCHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci, 
          nb_participation_events INT DEFAULT NULL, 
          adherent_uuid CHAR(36) DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:uuid)\', 
          UNIQUE INDEX UNIQ_55314F9525F06C53 (adherent_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          elected_representatives_register 
        ADD 
          CONSTRAINT FK_55314F9525F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
    }
}
