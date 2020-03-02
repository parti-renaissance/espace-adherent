<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200302100656 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vote_result_list_total_result (
          vote_result_id INT NOT NULL, 
          list_total_result_id INT NOT NULL, 
          INDEX IDX_BA0911E745EB7186 (vote_result_id), 
          INDEX IDX_BA0911E7586597E0 (list_total_result_id), 
          PRIMARY KEY(
            vote_result_id, list_total_result_id
          )
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote_result_list_collection (
          id INT AUTO_INCREMENT NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote_result_list_collection_city_proxy (
          id INT AUTO_INCREMENT NOT NULL, 
          list_collection_id INT DEFAULT NULL, 
          city_id INT UNSIGNED DEFAULT NULL, 
          INDEX IDX_1C21CEB3DB567AF4 (list_collection_id), 
          UNIQUE INDEX UNIQ_1C21CEB38BAC62AF (city_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE list_total_result (
          id INT AUTO_INCREMENT NOT NULL, 
          list_id INT DEFAULT NULL, 
          total INT DEFAULT 0 NOT NULL, 
          INDEX IDX_A19B071E3DAE168B (list_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote_result_list (
          id INT AUTO_INCREMENT NOT NULL, 
          list_collection_id INT DEFAULT NULL, 
          label VARCHAR(255) NOT NULL, 
          nuance VARCHAR(255) DEFAULT NULL, 
          adherent_count INT DEFAULT NULL, 
          eligible_count INT DEFAULT NULL, 
          INDEX IDX_677ED502DB567AF4 (list_collection_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          vote_result_list_total_result 
        ADD 
          CONSTRAINT FK_BA0911E745EB7186 FOREIGN KEY (vote_result_id) REFERENCES vote_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          vote_result_list_total_result 
        ADD 
          CONSTRAINT FK_BA0911E7586597E0 FOREIGN KEY (list_total_result_id) REFERENCES list_total_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          vote_result_list_collection_city_proxy 
        ADD 
          CONSTRAINT FK_1C21CEB3DB567AF4 FOREIGN KEY (list_collection_id) REFERENCES vote_result_list_collection (id)');
        $this->addSql('ALTER TABLE 
          vote_result_list_collection_city_proxy 
        ADD 
          CONSTRAINT FK_1C21CEB38BAC62AF FOREIGN KEY (city_id) REFERENCES cities (id)');
        $this->addSql('ALTER TABLE 
          list_total_result 
        ADD 
          CONSTRAINT FK_A19B071E3DAE168B FOREIGN KEY (list_id) REFERENCES vote_result_list (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          vote_result_list 
        ADD 
          CONSTRAINT FK_677ED502DB567AF4 FOREIGN KEY (list_collection_id) REFERENCES vote_result_list_collection (id)');
        $this->addSql('ALTER TABLE vote_result DROP lists');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vote_result_list_collection_city_proxy DROP FOREIGN KEY FK_1C21CEB3DB567AF4');
        $this->addSql('ALTER TABLE vote_result_list DROP FOREIGN KEY FK_677ED502DB567AF4');
        $this->addSql('ALTER TABLE vote_result_list_total_result DROP FOREIGN KEY FK_BA0911E7586597E0');
        $this->addSql('ALTER TABLE list_total_result DROP FOREIGN KEY FK_A19B071E3DAE168B');
        $this->addSql('DROP TABLE vote_result_list_total_result');
        $this->addSql('DROP TABLE vote_result_list_collection');
        $this->addSql('DROP TABLE vote_result_list_collection_city_proxy');
        $this->addSql('DROP TABLE list_total_result');
        $this->addSql('DROP TABLE vote_result_list');
        $this->addSql('ALTER TABLE vote_result ADD lists JSON NOT NULL');
    }
}
