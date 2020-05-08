<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200508235826 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD address_geocodable_hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE donations ADD address_geocodable_hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE events ADD address_geocodable_hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE citizen_projects ADD address_geocodable_hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE committees ADD address_geocodable_hash VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP address_geocodable_hash');
        $this->addSql('ALTER TABLE citizen_projects DROP address_geocodable_hash');
        $this->addSql('ALTER TABLE committees DROP address_geocodable_hash');
        $this->addSql('ALTER TABLE donations DROP address_geocodable_hash');
        $this->addSql('ALTER TABLE events DROP address_geocodable_hash');
    }
}
