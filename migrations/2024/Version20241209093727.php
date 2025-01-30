<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241209093727 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_562C7DA36FBC9426 ON adherents (tags(512))');
        $this->addSql('CREATE INDEX IDX_562C7DA37B00651C ON adherents (status)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_562C7DA36FBC9426 ON adherents');
        $this->addSql('DROP INDEX IDX_562C7DA37B00651C ON adherents');
    }
}
