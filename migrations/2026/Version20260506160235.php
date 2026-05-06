<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260506160235 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_targeted
                ADD
                  chunk_number INT UNSIGNED DEFAULT 0 NOT NULL,
                ADD
                  processing_status VARCHAR(255) DEFAULT 'pending' NOT NULL,
                ADD
                  processed_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
                ADD
                  error_message LONGTEXT DEFAULT NULL
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_646FE8E3537A132935818044 ON adherent_message_targeted (message_id, chunk_number)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_646FE8E3537A1329487D0A4C ON adherent_message_targeted (message_id, processing_status)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_646FE8E3537A132935818044 ON adherent_message_targeted');
        $this->addSql('DROP INDEX IDX_646FE8E3537A1329487D0A4C ON adherent_message_targeted');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_message_targeted
                DROP
                  chunk_number,
                DROP
                  processing_status,
                DROP
                  processed_at,
                DROP
                  error_message
            SQL);
    }
}
