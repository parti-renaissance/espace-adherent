<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200623204535 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative CHANGE gender gender VARCHAR(10) DEFAULT NULL');
        $this->addSql(<<<'SQL'
            UPDATE elected_representative
            SET gender = NULL
            WHERE gender NOT IN ('female', 'male', 'other')
SQL
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            UPDATE elected_representative
            SET gender = ''
            WHERE gender IS NULL
SQL
        );
        $this->addSql('ALTER TABLE 
          elected_representative CHANGE gender gender VARCHAR(10) NOT NULL COLLATE utf8_unicode_ci');
    }
}
