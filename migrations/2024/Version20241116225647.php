<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241116225647 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          messenger_messages
        CHANGE
          created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
        CHANGE
          available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
        CHANGE
          delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE
          rememberme_token
        CHANGE
          lastUsed lastUsed DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          messenger_messages
        CHANGE
          created_at created_at DATETIME NOT NULL,
        CHANGE
          available_at available_at DATETIME NOT NULL,
        CHANGE
          delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE rememberme_token CHANGE lastUsed lastUsed DATETIME NOT NULL');
    }
}
