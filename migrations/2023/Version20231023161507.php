<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231023161507 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation_poll_question CHANGE content content VARCHAR(500) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_election_pool CHANGE code code VARCHAR(500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation_poll_question CHANGE content content VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE voting_platform_election_pool CHANGE code code VARCHAR(255) NOT NULL');
    }
}
