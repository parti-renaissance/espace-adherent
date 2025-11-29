<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240221145728 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ohme_contact (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED DEFAULT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          ohme_identifier VARCHAR(255) NOT NULL,
          email VARCHAR(255) DEFAULT NULL,
          firstname VARCHAR(255) DEFAULT NULL,
          lastname VARCHAR(255) DEFAULT NULL,
          civility VARCHAR(255) DEFAULT NULL,
          birthdate DATE DEFAULT NULL,
          address_street VARCHAR(255) DEFAULT NULL,
          address_street2 VARCHAR(255) DEFAULT NULL,
          address_city VARCHAR(255) DEFAULT NULL,
          address_post_code VARCHAR(255) DEFAULT NULL,
          address_country VARCHAR(255) DEFAULT NULL,
          address_country_code VARCHAR(255) DEFAULT NULL,
          phone VARCHAR(255) DEFAULT NULL,
          ohme_created_at DATETIME DEFAULT NULL,
          ohme_updated_at DATETIME DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_A4E1D16CF82D7740 (ohme_identifier),
          UNIQUE INDEX UNIQ_A4E1D16CD17F50A6 (uuid),
          INDEX IDX_A4E1D16C25F06C53 (adherent_id),
          INDEX IDX_A4E1D16C9DF5350C (created_by_administrator_id),
          INDEX IDX_A4E1D16CCF1918FF (updated_by_administrator_id),
          INDEX IDX_A4E1D16CF82D7740 (ohme_identifier),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          ohme_contact
        ADD
          CONSTRAINT FK_A4E1D16C25F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          ohme_contact
        ADD
          CONSTRAINT FK_A4E1D16C9DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          ohme_contact
        ADD
          CONSTRAINT FK_A4E1D16CCF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ohme_contact DROP FOREIGN KEY FK_A4E1D16C25F06C53');
        $this->addSql('ALTER TABLE ohme_contact DROP FOREIGN KEY FK_A4E1D16C9DF5350C');
        $this->addSql('ALTER TABLE ohme_contact DROP FOREIGN KEY FK_A4E1D16CCF1918FF');
        $this->addSql('DROP TABLE ohme_contact');
    }
}
