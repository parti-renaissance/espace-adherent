<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180619112308 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SPATIAL INDEX district_geo_shape_idx ON districts (geo_shape)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX district_geo_shape_idx ON districts');
    }
}
