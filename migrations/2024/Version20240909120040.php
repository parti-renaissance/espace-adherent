<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240909120040 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transactional_email_template DROP FOREIGN KEY FK_65A0950A727ACA70');
        $this->addSql('ALTER TABLE
          transactional_email_template
        ADD
          CONSTRAINT FK_65A0950A727ACA70 FOREIGN KEY (parent_id) REFERENCES transactional_email_template (id) ON DELETE
        SET
          NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_65A0950A772E836A ON transactional_email_template (identifier)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE transactional_email_template DROP FOREIGN KEY FK_65A0950A727ACA70');
        $this->addSql('DROP INDEX UNIQ_65A0950A772E836A ON transactional_email_template');
        $this->addSql('ALTER TABLE
          transactional_email_template
        ADD
          CONSTRAINT FK_65A0950A727ACA70 FOREIGN KEY (parent_id) REFERENCES transactional_email_template (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
