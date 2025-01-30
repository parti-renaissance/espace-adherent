<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211130010431 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE pap_vote_place (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          latitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          longitude FLOAT (10, 6) DEFAULT NULL COMMENT \'(DC2Type:geo_point)\',
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          INDEX IDX_E143383F4118D12385E16F6B (latitude, longitude),
          UNIQUE INDEX UNIQ_E143383FD17F50A6 (uuid),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pap_address ADD vote_place_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          pap_address
        ADD
          CONSTRAINT FK_47071E11F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES pap_vote_place (id)');
        $this->addSql('CREATE INDEX IDX_47071E11F3F90B30 ON pap_address (vote_place_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pap_address DROP FOREIGN KEY FK_47071E11F3F90B30');
        $this->addSql('DROP TABLE pap_vote_place');
        $this->addSql('DROP INDEX IDX_47071E11F3F90B30 ON pap_address');
        $this->addSql('ALTER TABLE pap_address DROP vote_place_id');
    }
}
