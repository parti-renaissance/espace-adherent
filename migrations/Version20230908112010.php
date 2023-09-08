<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230908112010 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audience_segment DROP FOREIGN KEY FK_C5C2F52FF675F31B');
        $this->addSql('ALTER TABLE
          audience_segment
        ADD
          CONSTRAINT FK_C5C2F52FF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audience_segment DROP FOREIGN KEY FK_C5C2F52FF675F31B');
        $this->addSql('ALTER TABLE
          audience_segment
        ADD
          CONSTRAINT FK_C5C2F52FF675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
