<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190125100000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE jecoute_survey_question SET uuid = UUID() WHERE uuid = ''");
    }

    public function down(Schema $schema): void
    {
    }
}
