<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210604120338 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE push_token (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          adherent_id INT UNSIGNED NOT NULL,
          identifier VARCHAR(255) NOT NULL,
          source VARCHAR(255) NOT NULL,
          uuid CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\',
          created_at DATETIME NOT NULL,
          updated_at DATETIME NOT NULL,
          UNIQUE INDEX UNIQ_51BC1381772E836A (identifier),
          INDEX IDX_51BC138125F06C53 (adherent_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          push_token
        ADD
          CONSTRAINT FK_51BC138125F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE push_token');
    }
}
