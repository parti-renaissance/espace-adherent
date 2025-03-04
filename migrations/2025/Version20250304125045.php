<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250304125045 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          adherent_message_filters
        ADD
          first_membership_since DATE DEFAULT NULL,
        ADD
          first_membership_before DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE adherent_message_filters DROP first_membership_since, DROP first_membership_before');
    }
}
