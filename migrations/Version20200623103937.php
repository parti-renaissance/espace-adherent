<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200623103937 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_zone ADD code VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX elected_repr_zone_code ON elected_representative_zone (code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX elected_repr_zone_code ON elected_representative_zone');
        $this->addSql('ALTER TABLE elected_representative_zone DROP code');
    }
}
