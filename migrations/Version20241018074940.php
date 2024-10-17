<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241018074940 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents DROP notified_for_election');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherents ADD notified_for_election TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
