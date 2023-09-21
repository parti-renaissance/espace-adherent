<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230914080444 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation ADD wording_regulation_page_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE
          designation
        ADD
          CONSTRAINT FK_8947610DE3A77273 FOREIGN KEY (wording_regulation_page_id) REFERENCES cms_block (id)');
        $this->addSql('CREATE INDEX IDX_8947610DE3A77273 ON designation (wording_regulation_page_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE designation DROP FOREIGN KEY FK_8947610DE3A77273');
        $this->addSql('DROP INDEX IDX_8947610DE3A77273 ON designation');
        $this->addSql('ALTER TABLE designation DROP wording_regulation_page_id');
    }
}
