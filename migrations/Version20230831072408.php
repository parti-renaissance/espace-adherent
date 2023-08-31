<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230831072408 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_managed_users ADD zones_ids VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_90A7D656AB78BDC2 ON projection_managed_users (zones_ids)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_90A7D656AB78BDC2 ON projection_managed_users');
        $this->addSql('ALTER TABLE projection_managed_users DROP zones_ids');
    }
}
