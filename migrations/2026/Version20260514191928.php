<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260514191928 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_clients DROP deleted_at');
        $this->addSql('ALTER TABLE proposals DROP deleted_at');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oauth_clients ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE proposals ADD deleted_at DATETIME DEFAULT NULL');
    }
}
