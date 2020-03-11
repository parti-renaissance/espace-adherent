<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200311141459 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ministry_vote_result CHANGE voters participated INT NOT NULL');
        $this->addSql('ALTER TABLE vote_result CHANGE voters participated INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ministry_vote_result CHANGE participated voters INT NOT NULL');
        $this->addSql('ALTER TABLE vote_result CHANGE participated voters INT NOT NULL');
    }
}
