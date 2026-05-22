<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260522144858 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE chatbot_thread ADD agent VARCHAR(50) DEFAULT 'chatbot' NOT NULL");
        $this->addSql('ALTER TABLE chatbot_thread ALTER agent DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chatbot_thread DROP agent');
    }
}
