<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181114165123 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE jecoute_survey ADD city VARCHAR(255) DEFAULT ''");
        $this->addSql('ALTER TABLE jecoute_survey MODIFY city VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_survey DROP city');
    }
}
