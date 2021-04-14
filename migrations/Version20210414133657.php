<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210414133657 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE quick_action (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL,
          cause_id INT UNSIGNED NOT NULL,
          title VARCHAR(100) NOT NULL,
          url VARCHAR(255) DEFAULT NULL,
          INDEX IDX_AD416F1C66E2221E (cause_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          quick_action
        ADD
          CONSTRAINT FK_AD416F1C66E2221E FOREIGN KEY (cause_id) REFERENCES cause (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE quick_action');
    }
}
