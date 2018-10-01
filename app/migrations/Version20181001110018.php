<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181001110018 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees CHANGE members_counts members_count SMALLINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE citizen_projects CHANGE members_counts members_count SMALLINT UNSIGNED NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects CHANGE members_count members_counts SMALLINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE committees CHANGE members_count members_counts SMALLINT UNSIGNED NOT NULL');
    }
}
