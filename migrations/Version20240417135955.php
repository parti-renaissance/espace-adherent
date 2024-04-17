<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240417135955 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_matching_history ADD email_copy TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql(<<<'SQL'
                        UPDATE procuration_v2_matching_history
                        SET email_copy = 1
                        WHERE matcher_id IS NOT NULL
            SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE procuration_v2_matching_history DROP email_copy');
    }
}
