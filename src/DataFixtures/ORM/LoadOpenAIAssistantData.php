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
                $data['openAiId']
            );

            $manager->persist($assistant);

            $this->setReference($key, $assistant);
        }

        $manager->flush();
    }

    private function createAssistant(
        string $name,
        string $openAiId
    ): Assistant {
        $assistant = new Assistant();
        $assistant->name = $name;
        $assistant->openAiId = $openAiId;

        return $assistant;
    }

    private function getAssistants(): \Generator
    {
        yield 'openai-assistant-1' => [
            'name' => 'Assistant #1',
            'openAiId' => 'assistant-1-identifier',
        ];

        yield 'openai-assistant-2' => [
            'name' => 'Assistant #2',
            'openAiId' => 'assistant-2-identifier',
        ];
    }
}
