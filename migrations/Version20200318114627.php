<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200318114627 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE elected_representative_political_function');
        $this->addSql('ALTER TABLE elected_representative_political_function ADD mandate_id INT NOT NULL, DROP geographical_area');
        $this->addSql('ALTER TABLE elected_representative_political_function ADD CONSTRAINT FK_303BAF416C1129CD FOREIGN KEY (mandate_id) REFERENCES elected_representative_mandate (id)');
        $this->addSql('CREATE INDEX IDX_303BAF416C1129CD ON elected_representative_political_function (mandate_id)');
        $this->addSql('ALTER TABLE elected_representative_mandate ADD number SMALLINT DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE elected_representative_mandate DROP number');
        $this->addSql('ALTER TABLE elected_representative_political_function DROP FOREIGN KEY FK_303BAF416C1129CD');
        $this->addSql('DROP INDEX IDX_303BAF416C1129CD ON elected_representative_political_function');
        $this->addSql('ALTER TABLE elected_representative_political_function ADD geographical_area VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, DROP mandate_id');
    }
}
