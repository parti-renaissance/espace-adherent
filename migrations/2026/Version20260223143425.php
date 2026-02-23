<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260223143425 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  chatbot_thread
                ADD
                  title VARCHAR(255) DEFAULT NULL,
                CHANGE
                  chatbot_id chatbot_id INT UNSIGNED DEFAULT NULL
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chatbot_thread DROP title, CHANGE chatbot_id chatbot_id INT UNSIGNED NOT NULL');
    }
}
