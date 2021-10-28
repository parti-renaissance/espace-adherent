<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20211019172400 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        CHANGE
          team_phoning_national_manager_role phoning_manager_role TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('DROP INDEX team_type_name_unique ON team');
        $this->addSql('ALTER TABLE team DROP type');
        $this->addSql('CREATE UNIQUE INDEX team_name_unique ON team (name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherents
        CHANGE
          phoning_manager_role team_phoning_national_manager_role TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('DROP INDEX team_name_unique ON team');
        $this->addSql('ALTER TABLE team ADD type VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE UNIQUE INDEX team_type_name_unique ON team (type, name)');
    }
}
