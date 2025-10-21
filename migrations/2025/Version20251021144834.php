<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251021144834 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_39E44CAD537A13295F8A7F73 ON adherent_message_reach (message_id, source)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_39E44CAD537A13295F8A7F73 ON adherent_message_reach');
    }
}
