<?php

namespace Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170731164115 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('CREATE TABLE activity_subscriptions (id INT AUTO_INCREMENT NOT NULL, following_adherent_id INT UNSIGNED DEFAULT NULL, followed_adherent_id INT UNSIGNED DEFAULT NULL, subscribed_at DATETIME NOT NULL, unsubscribed_at DATETIME DEFAULT NULL, INDEX IDX_5A543C56016700F (following_adherent_id), INDEX IDX_5A543C57D7402F7 (followed_adherent_id), UNIQUE INDEX activity_subscriptions_unique (followed_adherent_id, following_adherent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE citizen_initiative_skills (citizen_initiative_id INT UNSIGNED NOT NULL, skill_id INT NOT NULL, INDEX IDX_F936A5506FBEFC74 (citizen_initiative_id), INDEX IDX_F936A5505585C142 (skill_id), PRIMARY KEY(citizen_initiative_id, skill_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activity_subscriptions ADD CONSTRAINT FK_5A543C56016700F FOREIGN KEY (following_adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE activity_subscriptions ADD CONSTRAINT FK_5A543C57D7402F7 FOREIGN KEY (followed_adherent_id) REFERENCES adherents (id)');
        $this->addSql('ALTER TABLE citizen_initiative_skills ADD CONSTRAINT FK_F936A5506FBEFC74 FOREIGN KEY (citizen_initiative_id) REFERENCES events (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE citizen_initiative_skills ADD CONSTRAINT FK_F936A5505585C142 FOREIGN KEY (skill_id) REFERENCES skills (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE events ADD type VARCHAR(255) NOT NULL, ADD interests LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', ADD expert_assistance_needed TINYINT(1) DEFAULT \'0\', ADD expert_assistance_description VARCHAR(255) DEFAULT NULL, ADD coaching_requested TINYINT(1) DEFAULT \'0\', ADD coaching_request_problem_description VARCHAR(255) DEFAULT NULL, ADD coaching_request_proposed_solution VARCHAR(255) DEFAULT NULL, ADD coaching_request_required_means VARCHAR(255) DEFAULT NULL, CHANGE is_for_legislatives is_for_legislatives TINYINT(1) DEFAULT \'0\'');
    }

    public function down(Schema $schema)
    {
        $this->addSql('DROP TABLE activity_subscriptions');
        $this->addSql('DROP TABLE citizen_initiative_skills');
        $this->addSql('ALTER TABLE events DROP type, DROP interests, DROP expert_assistance_needed, DROP expert_assistance_description, DROP coaching_requested, DROP coaching_request_problem_description, DROP coaching_request_proposed_solution, DROP coaching_request_required_means, CHANGE is_for_legislatives is_for_legislatives TINYINT(1) DEFAULT \'0\' NOT NULL');
    }
}
