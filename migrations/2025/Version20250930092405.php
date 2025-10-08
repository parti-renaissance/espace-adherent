<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250930092405 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_74A0958693151B82 ON app_hit (event_type)');
        $this->addSql('CREATE INDEX IDX_74A095865F8A7F73 ON app_hit (source)');
        $this->addSql('CREATE INDEX IDX_74A0958693151B825F8A7F73 ON app_hit (event_type, source)');
        $this->addSql('CREATE INDEX IDX_74A0958611CB6B3A ON app_hit (object_type)');
        $this->addSql('CREATE INDEX IDX_74A09586232D562B ON app_hit (object_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_74A0958693151B82 ON app_hit');
        $this->addSql('DROP INDEX IDX_74A095865F8A7F73 ON app_hit');
        $this->addSql('DROP INDEX IDX_74A0958693151B825F8A7F73 ON app_hit');
        $this->addSql('DROP INDEX IDX_74A0958611CB6B3A ON app_hit');
        $this->addSql('DROP INDEX IDX_74A09586232D562B ON app_hit');
    }
}
