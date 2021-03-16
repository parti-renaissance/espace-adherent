<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200914095246 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_election_result DROP FOREIGN KEY FK_67EFA0E4A708DAFF');
        $this->addSql('ALTER TABLE 
          voting_platform_election_result 
        ADD 
          CONSTRAINT FK_67EFA0E4A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE voting_platform_election_result DROP FOREIGN KEY FK_67EFA0E4A708DAFF');
        $this->addSql('ALTER TABLE 
          voting_platform_election_result 
        ADD 
          CONSTRAINT FK_67EFA0E4A708DAFF FOREIGN KEY (election_id) REFERENCES voting_platform_election (id)');
    }
}
