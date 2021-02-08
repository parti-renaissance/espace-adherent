<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Entity\Poll\Vote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPollData extends Fixture implements DependentFixtureInterface
{
    public const POLL_01_UUID = '8adca369-938c-450b-92e9-9c2b1f206fa3';
    public const POLL_01_CHOICE_01_UUID = 'dd429c8f-a07f-47ad-a424-b28058c4bf7d';
    public const POLL_01_CHOICE_02_UUID = '26aba15c-b49a-4cb7-99ef-585e12bcff50';
    public const POLL_01_CHOICE_03_UUID = 'c140e1fb-749c-4b13-97f6-327999004247';

    public function load(ObjectManager $manager)
    {
        $poll1 = $this->createPoll(
            self::POLL_01_UUID,
            'Plutôt thé ou café ?',
            new \DateTime('+1 day'),
            $this->getReference('administrator-2')
        );

        $poll1Choice1 = $this->createChoice(self::POLL_01_CHOICE_01_UUID, 'Thé');
        $poll1->addChoice($poll1Choice1);

        $poll1Choice2 = $this->createChoice(self::POLL_01_CHOICE_02_UUID, 'Café');
        $poll1->addChoice($poll1Choice2);

        $poll1Choice3 = $this->createChoice(self::POLL_01_CHOICE_03_UUID, "Ni l'un ni l'autre");
        $poll1->addChoice($poll1Choice3);

        $manager->persist($poll1);
        $manager->persist($this->createVote($poll1Choice2));
        $manager->persist($this->createVote($poll1Choice2, $this->getReference('adherent-1')));
        $manager->persist($this->createVote($poll1Choice2, $this->getReference('adherent-3')));
        $manager->persist($this->createVote($poll1Choice3));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdminData::class,
            LoadAdherentData::class,
        ];
    }

    private function createPoll(
        string $uuid,
        string $question,
        \DateTimeInterface $finishAt,
        Administrator $createdBy = null
    ): Poll {
        return new Poll(Uuid::fromString($uuid), $question, $finishAt, $createdBy);
    }

    private function createChoice(string $uuid, string $value): Choice
    {
        return new Choice(Uuid::fromString($uuid), $value);
    }

    private function createVote(Choice $choice, Adherent $adherent = null): Vote
    {
        return new Vote($choice, $adherent);
    }
}
