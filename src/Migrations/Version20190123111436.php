<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190123111436 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE citizen_projects ADD address_region VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE adherents ADD address_region VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE committees ADD address_region VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD address_region VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE donations ADD address_region VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP address_region');
        $this->addSql('ALTER TABLE citizen_projects DROP address_region');
        $this->addSql('ALTER TABLE committees DROP address_region');
        $this->addSql('ALTER TABLE donations DROP address_region');
        $this->addSql('ALTER TABLE events DROP address_region');
    }
}
