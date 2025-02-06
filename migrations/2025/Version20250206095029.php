<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250206095029 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE live_stream (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_administrator_id INT DEFAULT NULL,
          updated_by_administrator_id INT DEFAULT NULL,
          title VARCHAR(255) NOT NULL,
          description LONGTEXT NOT NULL,
          url VARCHAR(255) NOT NULL,
          begin_at DATETIME NOT NULL,
          finish_at DATETIME NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_93BF08C8D17F50A6 (uuid),
          INDEX IDX_93BF08C89DF5350C (created_by_administrator_id),
          INDEX IDX_93BF08C8CF1918FF (updated_by_administrator_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          live_stream
        ADD
          CONSTRAINT FK_93BF08C89DF5350C FOREIGN KEY (created_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
        $this->addSql('ALTER TABLE
          live_stream
        ADD
          CONSTRAINT FK_93BF08C8CF1918FF FOREIGN KEY (updated_by_administrator_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE live_stream DROP FOREIGN KEY FK_93BF08C89DF5350C');
        $this->addSql('ALTER TABLE live_stream DROP FOREIGN KEY FK_93BF08C8CF1918FF');
        $this->addSql('DROP TABLE live_stream');
    }
}
