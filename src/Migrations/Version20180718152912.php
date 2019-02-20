<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20180718152912 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE web_hooks ADD service VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription_type ADD external_id VARCHAR(64) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BBE247379F75D7B0 ON subscription_type (external_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE web_hooks DROP service');
        $this->addSql('ALTER TABLE subscription_type DROP external_id');
        $this->addSql('DROP INDEX UNIQ_BBE247379F75D7B0 ON subscription_type');
    }
}
