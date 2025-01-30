<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220315170237 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE jemengage_deep_link (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          label VARCHAR(255) NOT NULL,
          link VARCHAR(255) NOT NULL,
          social_title VARCHAR(255) DEFAULT NULL,
          social_description VARCHAR(255) DEFAULT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          dynamic_link VARCHAR(255) DEFAULT NULL,
          UNIQUE INDEX UNIQ_AB0E5282D17F50A6 (uuid),
          INDEX IDX_AB0E52829DF5350C (created_by_administrator_id),
          INDEX IDX_AB0E5282CF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          jemengage_deep_link
        ADD
          CONSTRAINT FK_AB0E52829DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          jemengage_deep_link
        ADD
          CONSTRAINT FK_AB0E5282CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE jemengage_deep_link');
    }
}
