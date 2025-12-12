<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251208175641 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE petition_signature ADD adherent_id INT UNSIGNED DEFAULT NULL');
        $this->addSql(<<<'SQL'
                ALTER TABLE
                  petition_signature
                ADD
                  CONSTRAINT FK_347C271025F06C53 FOREIGN KEY (adherent_id) REFERENCES adherents (id) ON DELETE
                SET
                  NULL
            SQL);
        $this->addSql('CREATE INDEX IDX_347C271025F06C53 ON petition_signature (adherent_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE petition_signature DROP FOREIGN KEY FK_347C271025F06C53');
        $this->addSql('DROP INDEX IDX_347C271025F06C53 ON petition_signature');
        $this->addSql('ALTER TABLE petition_signature DROP adherent_id');
    }
}
