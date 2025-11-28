<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Documents\DocumentHandler;
use App\Entity\Administrator;
use App\Entity\Document;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadDocumentData extends Fixture implements DependentFixtureInterface
{
    public const DOCUMENT_1_UUID = '29eec30e-8f30-41f9-87b9-821d275d19dc';
    public const DOCUMENT_2_UUID = '648e7b13-ef89-4b8a-8302-19c66654ed15';

    private Generator $faker;

    public function __construct(private readonly DocumentHandler $documentHandler)
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        /** @var Administrator $administrator */
        $administrator = $this->getReference('administrator-renaissance', Administrator::class);

        $document = $this->createDocument(self::DOCUMENT_1_UUID, 'Document #1');
        $document->setCreatedByAdministrator($administrator);
        $this->createFile($document);
        $manager->persist($document);

        $document = $this->createDocument(self::DOCUMENT_2_UUID, 'Document #2');
        $document->setCreatedByAdministrator($administrator);
        $this->createFile($document);
        $manager->persist($document);

        $manager->flush();
    }

    private function createDocument(string $uuid, string $title, bool $comment = true): Document
    {
        $document = new Document(Uuid::fromString($uuid));
        $document->title = $title;
        $document->comment = $comment ? $this->faker->text('200') : null;

        return $document;
    }

    private function createFile(Document $document): void
    {
        $document->file = new UploadedFile(
            __DIR__.'/../adherent_formations/formation.pdf',
            'Formation.pdf',
            'application/pdf',
            null,
            true
        );

        $this->documentHandler->handleFile($document);
    }

    public function getDependencies(): array
    {
        return [
            LoadAdminData::class,
        ];
    }
}
