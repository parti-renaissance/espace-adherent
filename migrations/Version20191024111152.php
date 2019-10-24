<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191024111152 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP citizen_project_creation_email_subscription_radius');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          adherents 
        ADD 
          citizen_project_creation_email_subscription_radius INT DEFAULT 10 NOT NULL');
    }
}
