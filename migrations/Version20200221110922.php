<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200221110922 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vote_result DROP FOREIGN KEY FK_1F8DB349F675F31B');
        $this->addSql('DROP INDEX IDX_1F8DB349F675F31B ON vote_result');
        $this->addSql('ALTER TABLE vote_result DROP author_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE vote_result ADD author_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE 
          vote_result 
        ADD 
          CONSTRAINT FK_1F8DB349F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id)');
        $this->addSql('CREATE INDEX IDX_1F8DB349F675F31B ON vote_result (author_id)');
    }
}
