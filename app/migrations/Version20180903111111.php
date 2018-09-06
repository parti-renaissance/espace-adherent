<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180903111111 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE citizen_projects SET image_name = \'default.png\' WHERE image_name IS NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE citizen_projects SET image_name = NULL WHERE image_name = \'default.png\'');
    }
}
