<?php

namespace Migrations;

use App\DataFixtures\ORM\LoadSkillData;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Version20170904153003 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function up(Schema $schema)
    {
        foreach (LoadSkillData::SKILL_CI as $skill) {
            $skillSlug = $this->container->get('sonata.core.slugify.cocur')->slugify($skill);
            $existingSkill = $this->connection->executeQuery('SELECT id FROM skills WHERE slug = :skillSlug', ['skillSlug' => $skillSlug]);

            if ($existingSkill->rowCount() < 1) {
                $this->connection->insert('skills', ['name' => $skill, 'slug' => $skillSlug]);
            }
        }
    }

    public function down(Schema $schema)
    {
    }
}
