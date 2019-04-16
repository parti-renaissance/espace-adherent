<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190415143923 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE assessor_requests_vote_place_wishes (
          assessor_request_id INT UNSIGNED NOT NULL, 
          vote_place_id INT NOT NULL, 
          INDEX IDX_1517FC131BD1903D (assessor_request_id), 
          INDEX IDX_1517FC13F3F90B30 (vote_place_id), 
          PRIMARY KEY(
            assessor_request_id, vote_place_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          assessor_requests_vote_place_wishes 
        ADD 
          CONSTRAINT FK_1517FC131BD1903D FOREIGN KEY (assessor_request_id) REFERENCES assessor_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          assessor_requests_vote_place_wishes 
        ADD 
          CONSTRAINT FK_1517FC13F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE assessor_request_vote_place_wishes');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE assessor_request_vote_place_wishes (
          assessor_request_id INT UNSIGNED NOT NULL, 
          vote_place_id INT NOT NULL, 
          INDEX IDX_2EFFDE111BD1903D (assessor_request_id), 
          INDEX IDX_2EFFDE11F3F90B30 (vote_place_id), 
          PRIMARY KEY(
            assessor_request_id, vote_place_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          assessor_request_vote_place_wishes 
        ADD 
          CONSTRAINT FK_2EFFDE111BD1903D FOREIGN KEY (assessor_request_id) REFERENCES assessor_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          assessor_request_vote_place_wishes 
        ADD 
          CONSTRAINT FK_2EFFDE11F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE assessor_requests_vote_place_wishes');
    }
}
