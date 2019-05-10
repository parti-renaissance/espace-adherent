<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190322110328 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE adherents SET legislative_candidate = 0');
        $this->addSql('ALTER TABLE unregistrations ADD excluded_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE 
          unregistrations 
        ADD 
          CONSTRAINT FK_F9E4AA0C5B30B80B FOREIGN KEY (excluded_by_id) REFERENCES administrators (id)');
        $this->addSql('CREATE INDEX IDX_F9E4AA0C5B30B80B ON unregistrations (excluded_by_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE unregistrations DROP FOREIGN KEY FK_F9E4AA0C5B30B80B');
        $this->addSql('DROP INDEX IDX_F9E4AA0C5B30B80B ON unregistrations');
        $this->addSql('ALTER TABLE unregistrations DROP excluded_by_id');
    }
}
