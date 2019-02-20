<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170901155126 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('ALTER TABLE summaries DROP FOREIGN KEY FK_66783CCA7597D3FE');
        $this->addSql('ALTER TABLE summaries ADD CONSTRAINT FK_66783CCA7597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema)
    {
        $this->addSql('ALTER TABLE summaries DROP FOREIGN KEY FK_66783CCA7597D3FE');
        $this->addSql('ALTER TABLE summaries ADD CONSTRAINT FK_66783CCA7597D3FE FOREIGN KEY (member_id) REFERENCES adherents (id)');
    }
}
