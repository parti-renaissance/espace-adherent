<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221003170009 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_message_filters
        CHANGE
          is_active_membership is_renaissance_membership TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_message_filters
        CHANGE
          is_renaissance_membership is_active_membership TINYINT(1) DEFAULT NULL');
    }
}
