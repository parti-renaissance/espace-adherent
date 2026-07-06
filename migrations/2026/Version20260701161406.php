<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701161406 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll_choice DROP FOREIGN KEY `FK_2DAE19C93C947C0F`');
        $this->addSql('ALTER TABLE poll_choice ADD CONSTRAINT FK_2DAE19C93C947C0F FOREIGN KEY (poll_id) REFERENCES poll (id)');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY `FK_ED568EBE998666D1`');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT FK_ED568EBE998666D1 FOREIGN KEY (choice_id) REFERENCES poll_choice (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE poll_choice DROP FOREIGN KEY FK_2DAE19C93C947C0F');
        $this->addSql('ALTER TABLE poll_choice ADD CONSTRAINT `FK_2DAE19C93C947C0F` FOREIGN KEY (poll_id) REFERENCES poll (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('ALTER TABLE poll_vote DROP FOREIGN KEY FK_ED568EBE998666D1');
        $this->addSql('ALTER TABLE poll_vote ADD CONSTRAINT `FK_ED568EBE998666D1` FOREIGN KEY (choice_id) REFERENCES poll_choice (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
