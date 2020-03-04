<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200303180519 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE city_vote_result (
          id INT AUTO_INCREMENT NOT NULL, 
          city_id INT UNSIGNED DEFAULT NULL, 
          election_round_id INT NOT NULL, 
          created_by_id INT UNSIGNED DEFAULT NULL, 
          updated_by_id INT UNSIGNED DEFAULT NULL, 
          registered INT NOT NULL, 
          abstentions INT NOT NULL, 
          voters INT NOT NULL, 
          expressed INT NOT NULL, 
          lists JSON NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          UNIQUE INDEX UNIQ_3C39AD468BAC62AF (city_id), 
          INDEX IDX_3C39AD46FCBF5E32 (election_round_id), 
          INDEX IDX_3C39AD46B03A8386 (created_by_id), 
          INDEX IDX_3C39AD46896DBBDE (updated_by_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          city_vote_result 
        ADD 
          CONSTRAINT FK_3C39AD468BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE 
          city_vote_result 
        ADD 
          CONSTRAINT FK_3C39AD46FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          city_vote_result 
        ADD 
          CONSTRAINT FK_3C39AD46B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          city_vote_result 
        ADD 
          CONSTRAINT FK_3C39AD46896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE city_vote_result');
    }
}
