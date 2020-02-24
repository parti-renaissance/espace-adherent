<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200224140456 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE election_rounds DROP FOREIGN KEY FK_37C02EA0A708DAFF');
        $this->addSql('ALTER TABLE 
          election_rounds 
        ADD 
          CONSTRAINT FK_37C02EA0A708DAFF FOREIGN KEY (election_id) REFERENCES elections (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349FCBF5E32');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE election_rounds DROP FOREIGN KEY FK_37C02EA0A708DAFF');
        $this->addSql('ALTER TABLE 
          election_rounds 
        ADD 
          CONSTRAINT FK_37C02EA0A708DAFF FOREIGN KEY (election_id) REFERENCES elections (id)');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349FCBF5E32');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id)');
    }
}
