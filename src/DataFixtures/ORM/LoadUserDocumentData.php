<?php

namespace App\DataFixtures\ORM;

use App\Entity\UserDocument;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadUserDocumentData extends Fixture
{
    public const USER_DOCUMENT_1_UUID = '5f279d90-712c-4335-a83b-a82851b43dfe';
    public const USER_DOCUMENT_2_UUID = 'd2abac78-6004-41cd-a88d-e3e1e83a6f65';

    public function load(ObjectManager $manager): void
    {
        $userDocumentPng = UserDocument::create(
            Uuid::fromString(self::USER_DOCUMENT_1_UUID),
            UserDocument::TYPE_EVENT,
            'image/png',
            'png_document.png',
            'png',
            1024
        );

        $userDocumentJpeg = UserDocument::create(
            Uuid::fromString(self::USER_DOCUMENT_2_UUID),
            UserDocument::TYPE_EVENT,
            'image/jpeg',
            'jpeg_document.jpeg',
            'jpeg',
            2048
        );

        $manager->persist($userDocumentPng);
        $manager->persist($userDocumentJpeg);

        $manager->flush();
    }
}
