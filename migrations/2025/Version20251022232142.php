<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251022232142 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE app_hit ADD source_group VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_74A09586F4C89FFA ON app_hit (source_group)');
        $this->addSql('CREATE INDEX IDX_74A0958693151B82F4C89FFA ON app_hit (event_type, source_group)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_74A09586F4C89FFA ON app_hit');
        $this->addSql('DROP INDEX IDX_74A0958693151B82F4C89FFA ON app_hit');
        $this->addSql('ALTER TABLE app_hit DROP source_group');
    }
}
