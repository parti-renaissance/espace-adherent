<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20201216190304 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          jecoute_news 
        ADD 
          zone_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          created_by_id INT DEFAULT NULL, 
        ADD 
          topic VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE 
          jecoute_news 
        ADD 
          CONSTRAINT FK_34362099F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('ALTER TABLE 
          jecoute_news 
        ADD 
          CONSTRAINT FK_3436209B03A8386 FOREIGN KEY (created_by_id) REFERENCES administrators (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_34362099F2C3FAB ON jecoute_news (zone_id)');
        $this->addSql('CREATE INDEX IDX_3436209B03A8386 ON jecoute_news (created_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_34362099F2C3FAB');
        $this->addSql('ALTER TABLE jecoute_news DROP FOREIGN KEY FK_3436209B03A8386');
        $this->addSql('DROP INDEX IDX_34362099F2C3FAB ON jecoute_news');
        $this->addSql('DROP INDEX IDX_3436209B03A8386 ON jecoute_news');
        $this->addSql('ALTER TABLE jecoute_news DROP zone_id, DROP created_by_id, DROP topic');
    }
}
