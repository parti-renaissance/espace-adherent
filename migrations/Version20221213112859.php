<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221213112859 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation ADD wording_welcome_page_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          designation
        ADD
          CONSTRAINT FK_8947610DDD49221F FOREIGN KEY (wording_welcome_page_id) REFERENCES cms_block (id)');
        $this->addSql('CREATE INDEX IDX_8947610DDD49221F ON designation (wording_welcome_page_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation DROP FOREIGN KEY FK_8947610DDD49221F');
        $this->addSql('DROP INDEX IDX_8947610DDD49221F ON designation');
        $this->addSql('ALTER TABLE designation DROP wording_welcome_page_id');
    }
}
