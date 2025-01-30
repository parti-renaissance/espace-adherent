<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221101210133 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX series ON rememberme_token');
        $this->addSql('ALTER TABLE
          rememberme_token
        CHANGE
          series series VARCHAR(88) NOT NULL,
        CHANGE
          value value VARCHAR(88) NOT NULL,
        CHANGE
          class class VARCHAR(100) NOT NULL,
        CHANGE
          username username VARCHAR(200) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          rememberme_token
        CHANGE
          series series CHAR(88) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          value value CHAR(88) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          class class VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          username username VARCHAR(200) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE UNIQUE INDEX series ON rememberme_token (series)');
    }
}
