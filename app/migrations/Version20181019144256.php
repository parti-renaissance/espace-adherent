<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181019144256 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_users_message ADD interests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', ADD gender VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE projection_referent_managed_users ADD interests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE projection_referent_managed_users DROP interests');
        $this->addSql('ALTER TABLE referent_managed_users_message DROP interests, DROP gender');
    }
}
