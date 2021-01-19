<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20210107173700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees DROP admin_comment, DROP coordinator_comment, DROP photo_uploaded');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committees ADD admin_comment LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD coordinator_comment LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD photo_uploaded TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
