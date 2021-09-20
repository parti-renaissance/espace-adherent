<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210920171534 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP phoning_campaign_call_more_status');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          need_email_renewal TINYINT(1) DEFAULT NULL,
        ADD
          need_sms_renewal TINYINT(1) DEFAULT NULL,
        ADD
          engagement VARCHAR(20) DEFAULT NULL,
        ADD
          note SMALLINT UNSIGNED DEFAULT NULL,
        DROP
          call_more,
        DROP
          need_renewal,
        DROP
          become_caller');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD phoning_campaign_call_more_status TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE
          phoning_campaign_history
        ADD
          call_more TINYINT(1) DEFAULT NULL,
        ADD
          need_renewal TINYINT(1) DEFAULT NULL,
        ADD
          become_caller TINYINT(1) DEFAULT NULL,
        DROP
          need_email_renewal,
        DROP
          need_sms_renewal,
        DROP
          engagement,
        DROP
          note');
    }
}
