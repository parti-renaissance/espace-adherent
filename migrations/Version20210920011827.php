<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210920011827 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE qr_code (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          created_by_id INT DEFAULT NULL,
          name VARCHAR(255) NOT NULL,
          redirect_url VARCHAR(255) NOT NULL,
          count INT NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          INDEX IDX_7D8B1FB5B03A8386 (created_by_id),
          UNIQUE INDEX qr_code_uuid (uuid),
          UNIQUE INDEX qr_code_name (name),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          qr_code
        ADD
          CONSTRAINT FK_7D8B1FB5B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE
        SET
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE qr_code');
    }
}
