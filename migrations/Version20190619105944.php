<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190619105944 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("
            UPDATE jecoute_survey SET jecoute_survey.type = 'local' 
            WHERE jecoute_survey.type = ''
            AND jecoute_survey.author_id IS NOT NULL
            AND jecoute_survey.administrator_id IS NULL
        ");
    }

    public function down(Schema $schema): void
    {
    }
}
