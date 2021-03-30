<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210326120302 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          cause_follower 
        ADD 
          zone_id INT UNSIGNED DEFAULT NULL, 
        ADD 
          first_name VARCHAR(50) DEFAULT NULL, 
        ADD 
          email_address VARCHAR(255) DEFAULT NULL, 
        ADD 
          cgu_accepted TINYINT(1) DEFAULT NULL, 
        ADD 
          cause_subscription TINYINT(1) DEFAULT NULL, 
        ADD 
          coalition_subscription TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          cause_follower 
        ADD 
          CONSTRAINT FK_6F9A85449F2C3FAB FOREIGN KEY (zone_id) REFERENCES geo_zone (id) ON DELETE 
        SET 
          NULL');
        $this->addSql('CREATE INDEX IDX_6F9A85449F2C3FAB ON cause_follower (zone_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cause_follower DROP FOREIGN KEY FK_6F9A85449F2C3FAB');
        $this->addSql('DROP INDEX IDX_6F9A85449F2C3FAB ON cause_follower');
        $this->addSql('ALTER TABLE 
          cause_follower 
        DROP 
          zone_id, 
        DROP 
          first_name, 
        DROP 
          email_address, 
        DROP 
          cgu_accepted, 
        DROP 
          cause_subscription, 
        DROP 
          coalition_subscription');
    }
}
