<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260319102656 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mailchimp_campaign CHANGE static_segment_id static_segment_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  mailchimp_campaign
                CHANGE
                  static_segment_id static_segment_id VARCHAR(255) DEFAULT NULL
            SQL);
    }
}
