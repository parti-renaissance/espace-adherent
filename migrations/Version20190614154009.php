<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20190614154009 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          application_request_running_mate CHANGE favorite_cities favorite_cities LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE 
          application_request_volunteer CHANGE favorite_cities favorite_cities LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\'');
    }

    public function postUp(Schema $schema): void
    {
        foreach (['application_request_running_mate', 'application_request_volunteer'] as $table) {
            $select = $this->connection->createQueryBuilder();
            $select
                ->select('id', 'favorite_cities')
                ->from($table)
            ;

            $update = $this->connection->createQueryBuilder();
            $update
                ->update($table, 'ar')
                ->set('ar.favorite_cities', ':favorite_cities')
                ->where('ar.id = :id')
            ;
            foreach ($select->execute() as $results) {
                $favoriteCities = json_decode($results['favorite_cities'], true);
                $update->setParameter('favorite_cities', implode(', ', $favoriteCities));
                $update->setParameter('id', $results['id']);
                $update->execute();
            }
        }
    }

    public function preDown(Schema $schema)
    {
        foreach (['application_request_running_mate', 'application_request_volunteer'] as $table) {
            $select = $this->connection->createQueryBuilder();
            $select
                ->select('id', 'favorite_cities')
                ->from($table)
            ;

            $update = $this->connection->createQueryBuilder();
            $update
                ->update($table, 'ar')
                ->set('ar.favorite_cities', ':favorite_cities')
                ->where('ar.id = :id')
            ;
            foreach ($select->execute() as $results) {
                $favoriteCities = explode(', ', $results['favorite_cities']);
                $i = \count($favoriteCities);
                while ($i > 0) {
                    $favoriteCities["$i"] = $favoriteCities[$i - 1];
                    --$i;
                }
                unset($favoriteCities[0]);
                $update->setParameter('favorite_cities', json_encode($favoriteCities));
                $update->setParameter('id', $results['id']);
                $update->execute();
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE 
          application_request_running_mate CHANGE favorite_cities favorite_cities JSON NOT NULL COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE 
          application_request_volunteer CHANGE favorite_cities favorite_cities JSON NOT NULL COMMENT \'(DC2Type:json_array)\'');
    }
}
