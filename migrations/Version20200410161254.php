<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200410161254 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ministry_list_total_result ADD outgoing_mayor TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE vote_result_list ADD outgoing_mayor TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ministry_list_total_result DROP outgoing_mayor');
        $this->addSql('ALTER TABLE vote_result_list DROP outgoing_mayor');
    }
}
