<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181212171515 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_comment ADD status VARCHAR(9) DEFAULT \'POSTED\' NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_thread CHANGE status status VARCHAR(9) DEFAULT \'POSTED\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_comment DROP status');
        $this->addSql('ALTER TABLE ideas_workshop_thread CHANGE status status VARCHAR(9) DEFAULT \'SUBMITTED\' NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
