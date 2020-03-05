<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200305174710 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_election DROP FOREIGN KEY FK_2CA406E5ED1A100B');
        $this->addSql('ALTER TABLE 
          committee_election 
        ADD 
          CONSTRAINT FK_2CA406E5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_election DROP FOREIGN KEY FK_2CA406E5ED1A100B');
        $this->addSql('ALTER TABLE 
          committee_election 
        ADD 
          CONSTRAINT FK_2CA406E5ED1A100B FOREIGN KEY (committee_id) REFERENCES committees (id)');
    }
}
