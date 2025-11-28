<?php

declare(strict_types=1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241016164551 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('UPDATE designation SET target = RIGHT(target, 4) WHERE target IS NOT NULL');
        $this->addSql('ALTER TABLE designation CHANGE target target_year SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE
          designation
        CHANGE
          target_year target LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
    }
}
