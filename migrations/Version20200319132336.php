<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200319132336 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vote_result_list DROP FOREIGN KEY FK_677ED502DB567AF4');
        $this->addSql('ALTER TABLE 
          vote_result_list 
        ADD 
          CONSTRAINT FK_677ED502DB567AF4 FOREIGN KEY (list_collection_id) REFERENCES vote_result_list_collection (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB3498BAC62AF');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349F3F90B30');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB3498BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB3498BAC62AF');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349F3F90B30');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB3498BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id)');
        $this->addSql('ALTER TABLE vote_result_list DROP FOREIGN KEY FK_677ED502DB567AF4');
        $this->addSql('ALTER TABLE 
          vote_result_list 
        ADD 
          CONSTRAINT FK_677ED502DB567AF4 FOREIGN KEY (list_collection_id) REFERENCES vote_result_list_collection (id)');
    }
}
