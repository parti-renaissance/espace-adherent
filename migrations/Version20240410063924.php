<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240410063924 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies ADD join_newsletter TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE procuration_v2_requests ADD join_newsletter TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_proxies DROP join_newsletter');
        $this->addSql('ALTER TABLE procuration_v2_requests DROP join_newsletter');
    }
}
