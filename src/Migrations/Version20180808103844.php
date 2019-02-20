<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20180808103844 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE turnkey_projects ADD is_approved TINYINT(1) DEFAULT \'0\' NOT NULL, DROP required_means');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE turnkey_projects ADD required_means LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, DROP is_approved');
    }
}
