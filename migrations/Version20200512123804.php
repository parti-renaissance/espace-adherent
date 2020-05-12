<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200512123804 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM elected_representative_political_function WHERE name = \'no_name\'');
    }

    public function down(Schema $schema): void
    {
    }
}
