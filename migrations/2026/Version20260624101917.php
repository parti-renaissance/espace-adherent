<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260624101917 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pronostic ADD image_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE pronostic ADD CONSTRAINT FK_E64BDCDE3DA5256D FOREIGN KEY (image_id) REFERENCES uploadable_file (id) ON DELETE SET NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E64BDCDE3DA5256D ON pronostic (image_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE pronostic DROP FOREIGN KEY FK_E64BDCDE3DA5256D');
        $this->addSql('DROP INDEX UNIQ_E64BDCDE3DA5256D ON pronostic');
        $this->addSql('ALTER TABLE pronostic DROP image_id');
    }
}
