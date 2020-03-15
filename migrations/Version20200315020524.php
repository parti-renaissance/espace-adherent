<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200315020524 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          candidate_option_prevision_id INT DEFAULT NULL, 
        ADD 
          third_option_prevision_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1354DEDE5 FOREIGN KEY (candidate_option_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          election_city_card 
        ADD 
          CONSTRAINT FK_EB01E8D1F543170A FOREIGN KEY (third_option_prevision_id) REFERENCES election_city_prevision (id) ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1354DEDE5 ON election_city_card (candidate_option_prevision_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EB01E8D1F543170A ON election_city_card (third_option_prevision_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1354DEDE5');
        $this->addSql('ALTER TABLE election_city_card DROP FOREIGN KEY FK_EB01E8D1F543170A');
        $this->addSql('DROP INDEX UNIQ_EB01E8D1354DEDE5 ON election_city_card');
        $this->addSql('DROP INDEX UNIQ_EB01E8D1F543170A ON election_city_card');
        $this->addSql('ALTER TABLE 
          election_city_card 
        DROP 
          candidate_option_prevision_id, 
        DROP 
          third_option_prevision_id');
    }
}
