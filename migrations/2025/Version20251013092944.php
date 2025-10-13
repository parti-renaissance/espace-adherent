<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251013092944 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign_report
                CHANGE
                  open_rate open_rate DOUBLE PRECISION NOT NULL,
                CHANGE
                  click_rate click_rate DOUBLE PRECISION NOT NULL,
                CHANGE
                  unsubscribed_rate unsubscribed_rate DOUBLE PRECISION NOT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign_report
                CHANGE
                  open_rate open_rate INT NOT NULL,
                CHANGE
                  click_rate click_rate INT NOT NULL,
                CHANGE
                  unsubscribed_rate unsubscribed_rate INT NOT NULL
            SQL);
    }
}
