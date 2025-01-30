<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220114151651 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_6579E8E7B669800E ON jecoute_data_survey (author_postal_code)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_6579E8E7B669800E ON jecoute_data_survey');
    }
}
