<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231106102259 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          projection_managed_users
        CHANGE
          adherent_tags additional_tags LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          projection_managed_users
        CHANGE
          additional_tags adherent_tags LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }
}
