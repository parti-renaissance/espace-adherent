<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190520172054 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE mailchimp_campaign_report (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          open_total INT NOT NULL, 
          open_unique INT NOT NULL, 
          open_rate INT NOT NULL, 
          last_open DATETIME DEFAULT NULL, 
          click_total INT NOT NULL, 
          click_unique INT NOT NULL, 
          click_rate INT NOT NULL, 
          last_click DATETIME DEFAULT NULL, 
          email_sent INT NOT NULL, 
          unsubscribed INT NOT NULL, 
          unsubscribed_rate INT NOT NULL, 
          created_at DATETIME NOT NULL, 
          updated_at DATETIME NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE mailchimp_campaign ADD report_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          mailchimp_campaign 
        ADD 
          CONSTRAINT FK_CFABD3094BD2A4C0 FOREIGN KEY (report_id) REFERENCES mailchimp_campaign_report (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CFABD3094BD2A4C0 ON mailchimp_campaign (report_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mailchimp_campaign DROP FOREIGN KEY FK_CFABD3094BD2A4C0');
        $this->addSql('DROP TABLE mailchimp_campaign_report');
        $this->addSql('DROP INDEX UNIQ_CFABD3094BD2A4C0 ON mailchimp_campaign');
        $this->addSql('ALTER TABLE mailchimp_campaign DROP report_id');
    }
}
