<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701161406 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE poll_participant (id INT UNSIGNED AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, poll_id INT UNSIGNED NOT NULL, adherent_id INT UNSIGNED NOT NULL, INDEX IDX_E5FD1C133C947C0F (poll_id), INDEX IDX_E5FD1C1325F06C53 (adherent_id), UNIQUE INDEX poll_participant_poll_adherent_unique (poll_id, adherent_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE poll_participant ADD CONSTRAINT FK_E5FD1C133C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE poll_participant ADD CONSTRAINT FK_E5FD1C1325F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE poll_choice DROP FOREIGN KEY `FK_2DAE19C93C947C0F`');
        $this->addSql('ALTER TABLE poll_choice ADD CONSTRAINT FK_2DAE19C93C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY `FK_ED568EBE998666D1`');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE998666D1 FOREIGN KEY (choice_id) REFERENCES poll_choice (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll_participant DROP FOREIGN KEY FK_E5FD1C133C947C0F');
        $this->addSql('ALTER TABLE poll_participant DROP FOREIGN KEY FK_E5FD1C1325F06C53');
        $this->addSql('DROP TABLE poll_participant');
        $this->addSql('ALTER TABLE poll_choice DROP FOREIGN KEY FK_2DAE19C93C947C0F');
        $this->addSql('ALTER TABLE poll_choice ADD CONSTRAINT `FK_2DAE19C93C947C0F` FOREIGN KEY (poll_id) REFERENCES poll (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE998666D1');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT `FK_ED568EBE998666D1` FOREIGN KEY (choice_id) REFERENCES poll_choice (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
