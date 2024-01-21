<?php

namespace App\DataFixtures\ORM;

use App\Entity\OpenAI\Assistant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadOpenAIAssistantData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        foreach ($this->getAssistants() as $key => $data) {
            $assistant = $this->createAssistant(
                $data['name'],
                $data['identifier']
            );

            $manager->persist($assistant);

            $this->setReference($key, $assistant);
        }

        $manager->flush();
    }

    private function createAssistant(
        string $name,
        string $identifier
    ): Assistant {
        $assistant = new Assistant();
        $assistant->name = $name;
        $assistant->identifier = $identifier;

        return $assistant;
    }

    private function getAssistants(): \Generator
    {
        yield 'openai-assistant-1' => [
            'name' => 'Assistant #1',
            'identifier' => 'assistant-1-identifier',
        ];

        yield 'openai-assistant-2' => [
            'name' => 'Assistant #2',
            'identifier' => 'assistant-2-identifier',
        ];
    }
}
