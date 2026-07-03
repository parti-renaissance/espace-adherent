<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260703124704 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY `FK_ED568EBE94A4C7D4`');
        $this->addSql('DROP INDEX IDX_ED568EBE94A4C7D4 ON poll_vote');
        $this->addSql('ALTER TABLE poll_vote ADD poll_id INT UNSIGNED DEFAULT NULL, DROP device_id');
        $this->addSql('UPDATE poll_vote AS vote
          INNER JOIN poll_choice AS choice ON choice.id = vote.choice_id
          SET vote.poll_id = choice.poll_id');
        $this->addSql('DELETE vote FROM poll_vote AS vote
          INNER JOIN poll_vote AS newer ON newer.poll_id = vote.poll_id
          AND newer.adherent_id = vote.adherent_id
          AND (newer.created_at > vote.created_at OR (newer.created_at = vote.created_at AND newer.id > vote.id))
          WHERE vote.adherent_id IS NOT NULL');
        $this->addSql('ALTER TABLE poll_vote CHANGE poll_id poll_id INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE3C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('CREATE INDEX IDX_ED568EBE3C947C0F ON poll_vote (poll_id)');
        $this->addSql('CREATE UNIQUE INDEX poll_vote_adherent_unique ON poll_vote (poll_id, adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE3C947C0F');
        $this->addSql('DROP INDEX IDX_ED568EBE3C947C0F ON poll_vote');
        $this->addSql('DROP INDEX poll_vote_adherent_unique ON poll_vote');
        $this->addSql('ALTER TABLE poll_vote ADD device_id INT UNSIGNED DEFAULT NULL, DROP poll_id');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT `FK_ED568EBE94A4C7D4` FOREIGN KEY (device_id) REFERENCES devices (id) ON UPDATE NO ACTION ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_ED568EBE94A4C7D4 ON poll_vote (device_id)');
    }
}
