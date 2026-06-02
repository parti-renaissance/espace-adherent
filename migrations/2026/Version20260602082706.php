<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260602082706 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('scopes')) {
            return;
        }

        $this->addSql("UPDATE scopes SET features = 'events,actions,ai_antiseche' WHERE code = 'militant'");
    }

    public function down(Schema $schema): void
    {
        if (!$schema->hasTable('scopes')) {
            return;
        }

        $this->addSql("UPDATE scopes SET features = 'events,actions' WHERE code = 'militant'");
    }
}
