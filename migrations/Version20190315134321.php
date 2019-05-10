<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190315134321 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE interactive_invitations DROP friend_first_name');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE interactive_invitations ADD friend_first_name VARCHAR(50) NOT NULL COLLATE utf8_unicode_ci');
    }
}
