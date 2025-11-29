<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\JeMengage\HeaderBlock;
use App\Image\ImageManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadHeaderBlockData extends Fixture
{
    private const MARKDOWN_CONTENT = <<<MARKDOWN
        Bienvenue {{ prenom }},
        Il reste {{ date_echeance }} jours avant le 1er tour des élections présidentielles !
        MARKDOWN;
    private const PREFIX = 'Je m\'engage avec';

    private ImageManagerInterface $imageManager;

    public function __construct(ImageManagerInterface $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createHeaderBlock(
            'page accueil',
            self::PREFIX,
            'la majorité présidentielle',
            self::MARKDOWN_CONTENT,
            new \DateTime('+2 weeks'),
            true
        ));

        $manager->persist($this->createHeaderBlock(
            'page connexion',
            self::PREFIX,
            'la majorité présidentielle',
            null,
            null,
            true
        ));

        $manager->flush();
    }

    private function createHeaderBlock(
        string $name,
        string $prefix,
        ?string $slogan = null,
        ?string $content = null,
        ?\DateTime $deadlineDate = null,
        bool $withImage = false,
    ): HeaderBlock {
        $headerBlock = new HeaderBlock($name, $prefix, $slogan, $content, $deadlineDate);

        if ($withImage) {
            $headerBlock->setImage(new UploadedFile(
                __DIR__.'/../coalitions/default.png',
                'image.png',
                'image/png',
                null,
                true
            ));
            $this->imageManager->saveImage($headerBlock);
        }

        return $headerBlock;
    }
}
