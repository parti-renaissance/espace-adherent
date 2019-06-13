<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190612173337 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          mailchimp_campaign 
        ADD 
          created_at DATETIME NOT NULL, 
        ADD 
          updated_at DATETIME NOT NULL');

        $this->addSql(
            'UPDATE mailchimp_campaign AS mc
            INNER JOIN adherent_messages AS am ON am.id = mc.message_id
            SET mc.created_at = am.created_at, mc.updated_at = am.updated_at'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE mailchimp_campaign DROP created_at, DROP updated_at');
    }
}
