<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230323111246 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP committee_uuids');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          committee_uuids LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }
}
