<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Jecoute\ResourceLink;
use App\Image\ImageManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadJecouteResourceLinkData extends Fixture
{
    public const RESOURCE_LINK_1_UUID = '94bc6088-ff8f-4d07-a216-6eebd12f317c';
    public const RESOURCE_LINK_2_UUID = '8ddd92d7-fc9e-43c0-8d03-57eccdce9547';
    public const RESOURCE_LINK_3_UUID = 'ae385b16-cf22-48c5-9a29-0b1a116d6643';

    private ImageManagerInterface $imageManager;

    public function __construct(ImageManagerInterface $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager): void
    {
        $resourceLink1 = $this->createResourceLink(
            self::RESOURCE_LINK_1_UUID,
            'On l\'a dit, On le fait',
            'https://transformer.en-marche.fr',
            true
        );

        $resourceLink2 = $this->createResourceLink(
            self::RESOURCE_LINK_2_UUID,
            'Ce qui a changé près de chez vous',
            'https://chezvous.en-marche.fr',
            true
        );

        $resourceLink3 = $this->createResourceLink(
            self::RESOURCE_LINK_3_UUID,
            '5 ans de plus',
            'https://5ansdeplus.fr',
            true
        );

        $manager->persist($resourceLink1);
        $manager->persist($resourceLink2);
        $manager->persist($resourceLink3);

        $manager->flush();
    }

    public function createResourceLink(string $uuid, string $label, string $url, bool $withImage = false): ResourceLink
    {
        $resource = new ResourceLink(Uuid::fromString($uuid), $label, $url);

        if ($withImage) {
            $resource->setImage(new UploadedFile(
                __DIR__.'/../coalitions/default.png',
                'image.png',
                'image/png',
                null,
                true
            ));
            $this->imageManager->saveImage($resource);
        }

        return $resource;
    }
}
