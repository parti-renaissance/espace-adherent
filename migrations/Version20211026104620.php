<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211026104620 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_address (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          number VARCHAR(255) DEFAULT NULL,
          address VARCHAR(255) DEFAULT NULL,
          insee_code VARCHAR(5) DEFAULT NULL,
          city_name VARCHAR(255) DEFAULT NULL,
          offset_x INT DEFAULT NULL,
          offset_y INT DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          INDEX IDX_47071E11D17F50A6 (uuid),
          INDEX IDX_47071E11D8AD1DD1AFAA2D47 (offset_x, offset_y),
          INDEX IDX_47071E114118D12385E16F6B (latitude, longitude),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pap_voter (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          address_id INT UNSIGNED NOT NULL,
          first_name VARCHAR(255) DEFAULT NULL,
          last_name VARCHAR(255) DEFAULT NULL,
          gender VARCHAR(255) DEFAULT NULL,
          birthdate DATE DEFAULT NULL,
          vote_place VARCHAR(10) DEFAULT NULL,
          source VARCHAR(5) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_FBF5A013F5B7AF75 (address_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          pap_voter
        ADD
          CONSTRAINT FK_FBF5A013F5B7AF75 FOREIGN KEY (address_id) REFERENCES pap_address (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_voter DROP FOREIGN KEY FK_FBF5A013F5B7AF75');
        $this->addSql('DROP TABLE pap_address');
        $this->addSql('DROP TABLE pap_voter');
    }
}
