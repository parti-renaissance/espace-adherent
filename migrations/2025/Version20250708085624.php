<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250708085624 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                DROP
                  INDEX IDX_D187C183D395B25E,
                ADD
                  UNIQUE INDEX UNIQ_D187C183D395B25E (filter_id)
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                CHANGE
                  type instance_scope VARCHAR(255) DEFAULT NULL,
                ADD
                  is_statutory TINYINT(1) DEFAULT 0 NOT NULL
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_D187C1837B00651C ON adherent_messages (status)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_D187C1835F8A7F73 ON adherent_messages (source)
            SQL);
        $this->addSql(<<<'SQL'
                CREATE INDEX IDX_D187C183C3144BB ON adherent_messages (instance_scope)
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                DROP
                  INDEX UNIQ_D187C183D395B25E,
                ADD
                  INDEX IDX_D187C183D395B25E (filter_id)
            SQL);
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  adherent_messages
                ADD
                  type VARCHAR(255) NOT NULL,
                DROP
                  instance_scope,
                DROP
                  is_statutory
            SQL);
    }
}
