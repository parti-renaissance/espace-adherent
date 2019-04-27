<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190426172030 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          referent_managed_users_message 
        ADD 
          registered_from DATE DEFAULT NULL, 
        ADD 
          registered_to DATE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE referent_managed_users_message DROP registered_from, DROP registered_to');
    }
}
