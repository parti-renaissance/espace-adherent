<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20210420154030 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_templates DROP FOREIGN KEY FK_6023E2A5F675F31B');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE email_templates DROP FOREIGN KEY FK_6023E2A5F675F31B');
        $this->addSql('ALTER TABLE
          email_templates
        ADD
          CONSTRAINT FK_6023E2A5F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE
        SET
          NULL');
    }
}
