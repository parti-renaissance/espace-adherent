<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211203111253 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE qr_code CHANGE redirect_url redirect_url LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE
          redirections
        CHANGE
          url_from url_from LONGTEXT NOT NULL,
        CHANGE
          url_to url_to LONGTEXT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          qr_code
        CHANGE
          redirect_url redirect_url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE
          redirections
        CHANGE
          url_from url_from VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`,
        CHANGE
          url_to url_to VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
