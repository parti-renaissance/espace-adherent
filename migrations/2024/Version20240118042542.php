<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240118042542 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          chatbot
        ADD
          telegram_bot_api_token VARCHAR(255) DEFAULT NULL,
        ADD
          telegram_bot_secret VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE chatbot_thread ADD telegram_chat_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chatbot DROP telegram_bot_api_token, DROP telegram_bot_secret');
        $this->addSql('ALTER TABLE chatbot_thread DROP telegram_chat_id');
    }
}
