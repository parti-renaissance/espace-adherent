<?php

namespace Migrations;

use AppBundle\Form\TypeExtension\TextTypeExtension;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20180131163109 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        foreach ($this->connection->executeQuery('SELECT id, first_name, last_name FROM adherents') as $adherent) {
            $this->connection->executeUpdate(
                'UPDATE adherents SET first_name = ?, last_name = ? WHERE id = ?',
                [
                    TextTypeExtension::formatIdentityCase($adherent['first_name']),
                    TextTypeExtension::formatIdentityCase($adherent['last_name']),
                    $adherent['id'],
                ],
                [
                    \PDO::PARAM_STR,
                    \PDO::PARAM_STR,
                    \PDO::PARAM_INT,
                ]
            );
        }

        foreach ($this->connection->executeQuery('SELECT id, `name`, subtitle FROM citizen_projects') as $project) {
            $this->connection->executeUpdate(
                'UPDATE citizen_projects SET `name` = ?, subtitle = ? WHERE id = ?',
                [
                    TextTypeExtension::formatTitleCase($project['name']),
                    TextTypeExtension::formatTitleCase($project['subtitle']),
                    $project['id'],
                ],
                [
                    \PDO::PARAM_STR,
                    \PDO::PARAM_STR,
                    \PDO::PARAM_INT,
                ]
            );
        }

        foreach ($this->connection->executeQuery('SELECT id, `name` FROM committees') as $committee) {
            $this->connection->executeUpdate(
                'UPDATE committees SET `name` = ? WHERE id = ?',
                [
                    TextTypeExtension::formatTitleCase($committee['name']),
                    $committee['id'],
                ],
                [
                    \PDO::PARAM_STR,
                    \PDO::PARAM_INT,
                ]
            );
        }

        foreach ($this->connection->executeQuery('SELECT id, `name` FROM events') as $event) {
            $this->connection->executeUpdate(
                'UPDATE events SET `name` = ? WHERE id = ?',
                [
                    TextTypeExtension::formatTitleCase($event['name']),
                    $event['id'],
                ],
                [
                    \PDO::PARAM_STR,
                    \PDO::PARAM_INT,
                ]
            );
        }
    }

    public function down(Schema $schema)
    {
        // no way down
    }
}
