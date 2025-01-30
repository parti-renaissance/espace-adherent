<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230623103320 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters ADD is_campus_registered TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_managed_users ADD campus_registered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP is_campus_registered');
        $this->addSql('ALTER TABLE projection_managed_users DROP campus_registered_at');
    }
}
