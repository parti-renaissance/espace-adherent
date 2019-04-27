<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190424105726 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages ADD send_to_timeline TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_messages DROP send_to_timeline');
    }
}
