<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200206223714 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vote_result (
          id INT AUTO_INCREMENT NOT NULL, 
          vote_place_id INT NOT NULL, 
          election_round_id INT NOT NULL, 
          author_id INT UNSIGNED NOT NULL, 
          registered INT NOT NULL, 
          abstentions INT NOT NULL, 
          voters INT NOT NULL, 
          expressed INT NOT NULL, 
          lists JSON NOT NULL, 
          UNIQUE INDEX UNIQ_1F8DB349F3F90B30 (vote_place_id), 
          INDEX IDX_1F8DB349FCBF5E32 (election_round_id), 
          INDEX IDX_1F8DB349F675F31B (author_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349F3F90B30 FOREIGN KEY (vote_place_id) REFERENCES vote_place (id)');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id)');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE vote_result');
    }
}
