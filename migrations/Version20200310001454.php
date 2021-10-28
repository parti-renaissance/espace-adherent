<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200310001454 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ministry_vote_result (
          id INT AUTO_INCREMENT NOT NULL, 
          election_round_id INT NOT NULL, 
          city_id INT UNSIGNED DEFAULT NULL, 
          created_by_id INT UNSIGNED DEFAULT NULL, 
          updated_by_id INT UNSIGNED DEFAULT NULL, 
          registered INT NOT NULL, 
          abstentions INT NOT NULL, 
          voters INT NOT NULL, 
          expressed INT NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          INDEX IDX_B9F11DAEFCBF5E32 (election_round_id), 
          UNIQUE INDEX UNIQ_B9F11DAE8BAC62AF (city_id), 
          INDEX IDX_B9F11DAEB03A8386 (created_by_id), 
          INDEX IDX_B9F11DAE896DBBDE (updated_by_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ministry_list_total_result (
          id INT AUTO_INCREMENT NOT NULL, 
          ministry_vote_result_id INT DEFAULT NULL, 
          label VARCHAR(255) NOT NULL, 
          nuance VARCHAR(255) DEFAULT NULL, 
          adherent_count INT DEFAULT NULL, 
          eligible_count INT DEFAULT NULL, 
          total INT DEFAULT 0 NOT NULL, 
          INDEX IDX_99D1332580711B75 (ministry_vote_result_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAEFCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAE8BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAEB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          ministry_list_total_result 
        ADD 
          CONSTRAINT FK_99D1332580711B75 FOREIGN KEY (ministry_vote_result_id) REFERENCES ministry_vote_result (id) ON DELETE CASCADE');
        $this->addSql('DROP TABLE city_vote_result');
        $this->addSql('DROP TABLE vote_result_list_collection_city_proxy');
        $this->addSql('ALTER TABLE vote_result_list_collection ADD city_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          vote_result_list_collection 
        ADD 
          CONSTRAINT FK_9C1DD9638BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9C1DD9638BAC62AF ON vote_result_list_collection (city_id)');
        $this->addSql('ALTER TABLE list_total_result DROP FOREIGN KEY FK_A19B071E45EB7186');
        $this->addSql('ALTER TABLE list_total_result CHANGE vote_result_id vote_result_id INT NOT NULL');
        $this->addSql('ALTER TABLE 
          list_total_result 
        ADD 
          CONSTRAINT FK_A19B071E45EB7186 FOREIGN KEY (vote_result_id) REFERENCES vote_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          city_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          type VARCHAR(255) NOT NULL, 
          CHANGE vote_place_id vote_place_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB3498BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('CREATE INDEX IDX_1F8DB3498BAC62AF ON vote_result (city_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ministry_list_total_result DROP FOREIGN KEY FK_99D1332580711B75');
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
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote_result_list_collection_city_proxy (
          id INT AUTO_INCREMENT NOT NULL, 
          list_collection_id INT DEFAULT NULL, 
          city_id INT UNSIGNED DEFAULT NULL, 
          UNIQUE INDEX UNIQ_1C21CEB38BAC62AF (city_id), 
          INDEX IDX_1C21CEB3DB567AF4 (list_collection_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          city_vote_result 
        ADD 
          CONSTRAINT FK_3C39AD46896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          city_vote_result 
        ADD 
          CONSTRAINT FK_3C39AD468BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE 
          city_vote_result 
        ADD 
          CONSTRAINT FK_3C39AD46B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          city_vote_result 
        ADD 
          CONSTRAINT FK_3C39AD46FCBF5E32 FOREIGN KEY (election_round_id) REFERENCES election_rounds (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          vote_result_list_collection_city_proxy 
        ADD 
          CONSTRAINT FK_1C21CEB38BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE 
          vote_result_list_collection_city_proxy 
        ADD 
          CONSTRAINT FK_1C21CEB3DB567AF4 FOREIGN KEY (list_collection_id) REFERENCES vote_result_list_collection (id)');
        $this->addSql('DROP TABLE ministry_vote_result');
        $this->addSql('DROP TABLE ministry_list_total_result');
        $this->addSql('ALTER TABLE list_total_result DROP FOREIGN KEY FK_A19B071E45EB7186');
        $this->addSql('ALTER TABLE list_total_result CHANGE vote_result_id vote_result_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          list_total_result 
        ADD 
          CONSTRAINT FK_A19B071E45EB7186 FOREIGN KEY (vote_result_id) REFERENCES vote_result (id)');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB3498BAC62AF');
        $this->addSql('DROP INDEX IDX_1F8DB3498BAC62AF ON vote_result');
        $this->addSql('ALTER TABLE 
          vote_result 
        DROP 
          city_id, 
        DROP 
          type, 
          CHANGE vote_place_id vote_place_id INT NOT NULL');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP FOREIGN KEY FK_9C1DD9638BAC62AF');
        $this->addSql('DROP INDEX UNIQ_9C1DD9638BAC62AF ON vote_result_list_collection');
        $this->addSql('ALTER TABLE vote_result_list_collection DROP city_id');
    }
}
