<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241204170446 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          administrator_action_history
        ADD
          telegram_notified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE
          user_action_history
        ADD
          telegram_notified_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE administrator_action_history DROP telegram_notified_at');
        $this->addSql('ALTER TABLE user_action_history DROP telegram_notified_at');
    }
}
