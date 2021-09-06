<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210906151345 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_90A7D656108B7592 ON projection_managed_users (original_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_90A7D656108B7592 ON projection_managed_users');
    }
}
