<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230306120354 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_A36198C6674D812 ON committees');
        $this->addSql('DROP INDEX UNIQ_A36198C6989D9B62 ON committees');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A36198C6674D812 ON committees (canonical_name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A36198C6989D9B62 ON committees (slug)');
    }
}
