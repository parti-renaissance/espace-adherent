<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220429114235 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          created_at DATETIME DEFAULT NOW(),
        ADD
          updated_at DATETIME DEFAULT NOW(),
        ADD
          audience_type VARCHAR(255) DEFAULT NULL,
        CHANGE
          scope scope VARCHAR(255) DEFAULT NULL');

        $this->addSql('ALTER TABLE
            adherent_message_filters
        CHANGE created_at created_at DATETIME NOT NULL,
        CHANGE updated_at updated_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_message_filters
        DROP
          created_at,
        DROP
          updated_at,
        DROP
          audience_type,
        CHANGE
          scope scope VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
