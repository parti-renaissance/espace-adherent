<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190807170109 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_users_message RENAME COLUMN "offset" TO offset_count');
        $this->addSql('ALTER TABLE deputy_managed_users_message RENAME COLUMN "offset" TO offset_count');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_users_message RENAME COLUMN offset_count TO "offset"');
        $this->addSql('ALTER TABLE deputy_managed_users_message RENAME COLUMN offset_count TO "offset"');
    }
}
