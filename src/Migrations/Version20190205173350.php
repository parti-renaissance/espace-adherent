<?php

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20190205173350 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_theme ADD position SMALLINT UNSIGNED NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        $position = 1;

        foreach ($this->connection->fetchAll('SELECT id FROM ideas_workshop_theme ORDER BY name ASC') as $themeId) {
            $this->connection->executeUpdate(
                sprintf('UPDATE ideas_workshop_theme SET position = %d WHERE id = %s', $position++, $themeId['id'])
            );
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ideas_workshop_theme DROP position');
    }
}
