<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251107170113 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_A250FF6C8278FE91 ON user_documents (instance_key)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_A250FF6C8278FE91 ON user_documents');
    }
}
