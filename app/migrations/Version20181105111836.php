<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181105111836 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_users_message ADD first_name VARCHAR(50) DEFAULT NULL, ADD last_name VARCHAR(50) DEFAULT NULL, ADD age_minimum INT DEFAULT NULL, ADD age_maximum INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_users_message DROP first_name, DROP last_name, DROP age_minimum, DROP age_maximum');
    }
}
