<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250703081536 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_562C7DA317BD45F1 ON adherents (mailchimp_status)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                DROP INDEX IDX_562C7DA317BD45F1 ON adherents
            SQL);
    }
}
