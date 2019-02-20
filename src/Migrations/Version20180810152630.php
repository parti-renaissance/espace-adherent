<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180810152630 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX citizen_project_canonical_name_unique ON citizen_projects');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX citizen_project_canonical_name_unique ON citizen_projects (canonical_name)');
    }
}
