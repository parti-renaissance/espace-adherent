<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181026150459 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users ADD citizen_projects JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE referent_managed_users_message ADD include_cp TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE projection_referent_managed_users ADD citizen_projects_organizer JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users DROP citizen_projects');
        $this->addSql('ALTER TABLE referent_managed_users_message DROP include_cp');
        $this->addSql('ALTER TABLE projection_referent_managed_users DROP citizen_projects_organizer');
    }
}
