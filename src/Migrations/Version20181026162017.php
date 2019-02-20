<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20181026162017 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_merge_histories ADD merged_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE committee_merge_histories ADD CONSTRAINT FK_BB95FBBC50FA8329 FOREIGN KEY (merged_by_id) REFERENCES administrators (id)');
        $this->addSql('CREATE INDEX IDX_BB95FBBC50FA8329 ON committee_merge_histories (merged_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE committee_merge_histories DROP FOREIGN KEY FK_BB95FBBC50FA8329');
        $this->addSql('DROP INDEX IDX_BB95FBBC50FA8329 ON committee_merge_histories');
        $this->addSql('ALTER TABLE committee_merge_histories DROP merged_by_id');
    }
}
