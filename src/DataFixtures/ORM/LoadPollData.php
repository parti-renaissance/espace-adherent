<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Poll\Choice;
use App\Entity\Poll\Poll;
use App\Entity\Poll\PollResultDisplayModeEnum;
use App\Entity\Poll\Vote;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class LoadPollData extends Fixture implements DependentFixtureInterface
{
    public const POLL_01_UUID = '8adca369-938c-450b-92e9-9c2b1f206fa3';
    public const POLL_06_UUID = '8bfce93c-f3b7-427f-8198-72e86a0009d9';
    public const POLL_07_UUID = '8bc98c61-5d57-4404-82d4-cd7dc5ff4434';
    public const POLL_01_CHOICE_01_UUID = 'dd429c8f-a07f-47ad-a424-b28058c4bf7d';
    public const POLL_01_CHOICE_02_UUID = '26aba15c-b49a-4cb7-99ef-585e12bcff50';
    public const POLL_01_CHOICE_03_UUID = 'c140e1fb-749c-4b13-97f6-327999004247';
    public const POLL_06_CHOICE_01_UUID = 'df3df915-4997-466a-bd0e-bc5f8ddb88a3';
    public const POLL_06_CHOICE_02_UUID = '5b01ded5-489e-4487-91cc-342476736cc9';

    public function load(ObjectManager $manager): void
    {
        $michelle = $this->getReference('adherent-1', Adherent::class);
        $jacques = $this->getReference('adherent-3', Adherent::class);
        $lucie = $this->getReference('adherent-4', Adherent::class);
        $francis = $this->getReference('adherent-7', Adherent::class);
        $bob = $this->getReference('senator-59', Adherent::class);

        $poll1 = $this->createPoll(
            self::POLL_01_UUID,
            'Plutôt thé ou café ?',
            new \DateTimeImmutable('+1 day')
        );

        $poll1Choice1 = $this->createChoice('Thé', self::POLL_01_CHOICE_01_UUID);
        $poll1->addChoice($poll1Choice1);

        $poll1Choice2 = $this->createChoice('Café', self::POLL_01_CHOICE_02_UUID);
        $poll1->addChoice($poll1Choice2);

        $poll1Choice3 = $this->createChoice("Ni l'un ni l'autre", self::POLL_01_CHOICE_03_UUID);
        $poll1->addChoice($poll1Choice3);

        $manager->persist($poll1);
        $manager->persist($this->createVote($poll1Choice2, $michelle));
        $manager->persist($this->createVote($poll1Choice2, $jacques));
        $manager->persist($this->createVote($poll1Choice2, $lucie));
        $manager->persist($this->createVote($poll1Choice3, $francis));

        $poll6 = $this->createPoll(
            self::POLL_06_UUID,
            'Sondage terminé ?',
            new \DateTimeImmutable('-1 day'),
            new \DateTimeImmutable('-2 day'),
            new \DateTimeImmutable('+1 day'),
            PollResultDisplayModeEnum::AFTER_POLL
        );

        $poll6Choice1 = $this->createChoice('Oui', self::POLL_06_CHOICE_01_UUID);
        $poll6->addChoice($poll6Choice1);

        $poll6Choice2 = $this->createChoice('Non', self::POLL_06_CHOICE_02_UUID);
        $poll6->addChoice($poll6Choice2);

        $poll7 = $this->createPoll(
            self::POLL_07_UUID,
            'Sondage à venir ?',
            new \DateTimeImmutable('+4 day'),
            new \DateTimeImmutable('+2 day')
        );
        $poll7->addChoice($this->createChoice('Oui'));
        $poll7->addChoice($this->createChoice('Non'));

        $manager->persist($poll6);
        $manager->persist($this->createVote($poll6Choice1, $lucie));
        $manager->persist($this->createVote($poll6Choice1, $francis));
        $manager->persist($this->createVote($poll6Choice1, $bob));
        $manager->persist($this->createVote($poll6Choice2, $michelle));
        $manager->persist($poll7);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }

    private function createPoll(
        string $uuid,
        string $question,
        \DateTimeImmutable $finishAt,
        ?\DateTimeImmutable $startAt = null,
        ?\DateTimeImmutable $resultDisplayEndAt = null,
        PollResultDisplayModeEnum $resultDisplayMode = PollResultDisplayModeEnum::AFTER_VOTE,
    ): Poll {
        return new Poll(
            Uuid::fromString($uuid),
            $question,
            $finishAt,
            true,
            $startAt,
            $resultDisplayEndAt,
            resultDisplayMode: $resultDisplayMode
        );
    }

    private function createChoice(string $value, ?string $uuid = null): Choice
    {
        return new Choice($value, $uuid ? Uuid::fromString($uuid) : null);
    }

    private function createVote(Choice $choice, Adherent $adherent): Vote
    {
        return new Vote($choice, $adherent);
    }
}
