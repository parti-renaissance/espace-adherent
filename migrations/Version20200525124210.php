<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200525124210 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAE896DBBDE');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAEB03A8386');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAEB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349896DBBDE');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349B03A8386');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id) ON DELETE 
        SET 
          NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAEB03A8386');
        $this->addSql('ALTER TABLE ministry_vote_result DROP FOREIGN KEY FK_B9F11DAE896DBBDE');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAEB03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          ministry_vote_result 
        ADD 
          CONSTRAINT FK_B9F11DAE896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349B03A8386');
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349896DBBDE');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349B03A8386 FOREIGN KEY (created_by_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349896DBBDE FOREIGN KEY (updated_by_id) REFERENCES adherents (id)');
    }
}
