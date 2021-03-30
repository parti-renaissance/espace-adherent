<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20181219114610 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_vote DROP FOREIGN KEY FK_9A9B535325F06C53');
        $this->addSql('DROP INDEX IDX_9A9B535325F06C53 ON ideas_workshop_vote');
        $this->addSql('ALTER TABLE ideas_workshop_vote CHANGE adherent_id author_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_vote ADD CONSTRAINT FK_9A9B5353F675F31B FOREIGN KEY (author_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9A9B5353F675F31B ON ideas_workshop_vote (author_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_vote DROP FOREIGN KEY FK_9A9B5353F675F31B');
        $this->addSql('DROP INDEX IDX_9A9B5353F675F31B ON ideas_workshop_vote');
        $this->addSql('ALTER TABLE ideas_workshop_vote CHANGE author_id adherent_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE ideas_workshop_vote ADD CONSTRAINT FK_9A9B535325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_9A9B535325F06C53 ON ideas_workshop_vote (adherent_id)');
    }
}
