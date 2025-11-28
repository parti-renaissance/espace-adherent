<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\LocalElection\CandidaciesGroup;
use App\Entity\LocalElection\Candidacy;
use App\Entity\LocalElection\LocalElection;
use App\Entity\LocalElection\SubstituteCandidacy;
use App\Entity\VotingPlatform\Designation\Designation;
use App\LocalElection\Manager;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadLocalElectionData extends Fixture implements DependentFixtureInterface
{
    public const UUID1 = '27498546-d3b3-4c4c-a138-f7f1358ba5f1';

    public function __construct(private readonly Manager $localElectionManager, private ?Generator $faker = null)
    {
        $this->faker = Factory::create('FR_fr');
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($object = new LocalElection($this->getReference('designation-13', Designation::class), Uuid::fromString(self::UUID1)));
        $this->fillLists($object, 5, 12);

        $this->setReference('local-election-1', $object);

        foreach (['06', '77', '93'] as $department) {
            $manager->persist($election = new LocalElection($this->getReference("designation-local-dpt-$department", Designation::class), Uuid::uuid4()));
            $this->fillLists($election, 5, 12);

            $this->setReference("local-election-dpt-$department", $object);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadDesignationData::class,
        ];
    }

    private function fillLists(LocalElection $election, int $totalLists, int $totalCandidates): void
    {
        for ($listIndex = 1; $listIndex <= $totalLists; ++$listIndex) {
            $election->addCandidaciesGroup($list = new CandidaciesGroup());

            $list->file = new UploadedFile(
                __DIR__.'/../../../app/data/files/application_requests/curriculum/cv.pdf',
                'profession-de-foi.pdf',
                'application/pdf',
                null,
                true
            );

            $this->localElectionManager->uploadFaithStatementFile($list);

            for ($candidateIndex = 1; $candidateIndex <= $totalCandidates; ++$candidateIndex) {
                if (0 === $candidateIndex % 2) {
                    $gender = Genders::FEMALE;
                    $firstName = $this->faker->firstNameFemale();
                } else {
                    $gender = Genders::MALE;
                    $firstName = $this->faker->firstNameMale();
                }
                $lastName = $this->faker->lastName();

                $list->addCandidacy($candidate = new Candidacy($election, $gender));
                $candidate->setPosition($candidateIndex);
                $candidate->setFirstName($firstName);
                $candidate->setLastName($lastName);
                $candidate->setEmail($this->faker->freeEmail());
            }

            $list->addSubstituteCandidacy($candidate = new SubstituteCandidacy($election, Genders::FEMALE));
            $candidate->setPosition(1);
            $candidate->setFirstName($this->faker->firstNameFemale());
            $candidate->setLastName($this->faker->lastName());
            $candidate->setEmail($this->faker->freeEmail());

            $list->addSubstituteCandidacy($candidate = new SubstituteCandidacy($election, Genders::MALE));
            $candidate->setPosition(2);
            $candidate->setFirstName($this->faker->firstNameMale());
            $candidate->setLastName($this->faker->lastName());
            $candidate->setEmail($this->faker->freeEmail());
        }
    }
}
