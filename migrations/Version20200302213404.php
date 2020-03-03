<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200302213404 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE vote_result_list_total_result');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          created_by_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          updated_by_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          created_at DATETIME DEFAULT NULL, 
        ADD 
          updated_at DATETIME DEFAULT NULL');

        $this->addSql('UPDATE vote_result SET created_at = NOW(), updated_at = NOW()');

        $this->addSql('ALTER TABLE 
          vote_result 
        CHANGE 
          created_at created_at DATETIME NOT NULL, 
        CHANGE 
          updated_at updated_at DATETIME NOT NULL');

        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_1F8DB349B03A8386 ON vote_result (created_by_id)');
        $this->addSql('CREATE INDEX IDX_1F8DB349896DBBDE ON vote_result (updated_by_id)');
        $this->addSql('ALTER TABLE list_total_result ADD vote_result_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          list_total_result 
        ADD 
          CONSTRAINT FK_A19B071E45EB7186 FOREIGN KEY (vote_result_id) REFERENCES vote_result (id)');
        $this->addSql('CREATE INDEX IDX_A19B071E45EB7186 ON list_total_result (vote_result_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE vote_result_list_total_result (
          vote_result_id INT NOT NULL, 
          list_total_result_id INT NOT NULL, 
          INDEX IDX_BA0911E745EB7186 (vote_result_id), 
          INDEX IDX_BA0911E7586597E0 (list_total_result_id), 
          PRIMARY KEY(
            vote_result_id, list_total_result_id
          )
        ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          vote_result_list_total_result 
        ADD 
          CONSTRAINT FK_BA0911E745EB7186 FOREIGN KEY (vote_result_id) REFERENCES vote_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE 
          vote_result_list_total_result 
        ADD 
          CONSTRAINT FK_BA0911E7586597E0 FOREIGN KEY (list_total_result_id) REFERENCES list_total_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE list_total_result DROP FOREIGN KEY FK_A19B071E45EB7186');
        $this->addSql('DROP INDEX IDX_A19B071E45EB7186 ON list_total_result');
        $this->addSql('ALTER TABLE list_total_result DROP vote_result_id');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349B03A8386');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349896DBBDE');
        $this->addSql('DROP INDEX IDX_1F8DB349B03A8386 ON vote_result');
        $this->addSql('DROP INDEX IDX_1F8DB349896DBBDE ON vote_result');
        $this->addSql('ALTER TABLE 
          vote_result 
        DROP 
          created_by_id, 
        DROP 
          updated_by_id, 
        DROP 
          created_at, 
        DROP 
          updated_at');
    }
}
