<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

final class Version20200904110248 extends AbstractMigration
{
    private const DEFAULT_ROLES = [
        'Responsable communication',
        'Responsable mobilisation',
        'Responsable phoning',
    ];

    private const DEFAULT_REFERENT_ROLES = [
        'Responsables territoriaux' => [
            'Responsable des Comités',
            'Responsable Logistique',
            'Responsable Mobilisation',
            'Responsable Talents / Formation',
        ],
        'Responsables communications et digitaux' => [
            'Responsable Communication',
            'Responsable Réseaux Sociaux',
            'Responsable Digital',
            'Responsable Contenu',
        ],
        'Responsables thématiques' => [
            'Responsable Engagement Citoyen',
            'Responsable Europe',
            'Responsable Société Civile',
            'Responsable Idée',
        ],
        'Responsables administratifs' => [
            'Responsable Financier',
            'Secrétaire Général',
            'Responsable Élus',
            'Responsable des Elections',
            'Responsable procurations',
            'Responsable bureaux de vote / assesseurs',
        ],
        'Opération #AVosCotes' => [
            'Appelant',
        ],
    ];

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE my_team_delegated_access_role (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          group_id INT UNSIGNED DEFAULT NULL, 
          type VARCHAR(255) NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          INDEX IDX_82441FF6FE54D947 (group_id), 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE my_team_delegated_access_group (
          id INT UNSIGNED AUTO_INCREMENT NOT NULL, 
          name VARCHAR(255) NOT NULL, 
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE 
          my_team_delegated_access_role 
        ADD 
          CONSTRAINT FK_82441FF6FE54D947 FOREIGN KEY (group_id) REFERENCES my_team_delegated_access_group (id) ON DELETE CASCADE');

        // todo need to migrate existant before doing this
        $this->addSql('ALTER TABLE my_team_delegated_access ADD role_id INT UNSIGNED DEFAULT NULL, DROP role');
        $this->addSql('ALTER TABLE 
          my_team_delegated_access 
        ADD 
          CONSTRAINT FK_421C13B9D60322AC FOREIGN KEY (role_id) REFERENCES my_team_delegated_access_role (id)');
        $this->addSql('CREATE INDEX IDX_421C13B9D60322AC ON my_team_delegated_access (role_id)');

        foreach (['deputy', 'senator', 'municipal_chief'] as $type) {
            foreach (self::DEFAULT_ROLES as $role) {
                $this->addSql(
                    'INSERT INTO my_team_delegated_access_role (type, name) VALUES (:type, :role)',
                    ['type' => $type, 'role' => $role]
                );
            }
        }

        foreach (self::DEFAULT_REFERENT_ROLES as $group => $roles) {
            $this->addSql('INSERT INTO my_team_delegated_access_group (name) VALUES (:group)', ['group' => $group]);
            foreach ($roles as $role) {
                $this->addSql(
                    'INSERT INTO my_team_delegated_access_role (type, name, group_id) VALUES ("referent", :role, (select max(id) from my_team_delegated_access_group))',
                    ['role' => $role]
                );
            }
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE my_team_delegated_access DROP FOREIGN KEY FK_421C13B9D60322AC');
        $this->addSql('ALTER TABLE my_team_delegated_access_role DROP FOREIGN KEY FK_82441FF6FE54D947');
        $this->addSql('DROP TABLE my_team_delegated_access_role');
        $this->addSql('DROP TABLE my_team_delegated_access_group');
        $this->addSql('DROP INDEX IDX_421C13B9D60322AC ON my_team_delegated_access');
        $this->addSql('ALTER TABLE 
          my_team_delegated_access 
        ADD 
          role VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, 
        DROP 
          role_id');
    }
}
