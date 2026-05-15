<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Video;
use App\Entity\VideoStatusEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Uid\Uuid;

class LoadVideoData extends Fixture
{
    public const string VIDEO_PORTRAIT_1080_UUID = '550e8400-e29b-41d4-a716-446655440000';
    public const string VIDEO_PORTRAIT_720_UUID = '550e8400-e29b-41d4-a716-446655440001';
    public const string VIDEO_SQUARE_1080_UUID = '550e8400-e29b-41d4-a716-446655440002';
    public const string VIDEO_PENDING_UUID = '550e8400-e29b-41d4-a716-446655440099';

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->buildVideo(
            uuid: self::VIDEO_PORTRAIT_1080_UUID,
            folder: 'case_1_9x16_1080',
            title: 'POC — Portrait 9:16 1080p',
            duration: 7,
            width: 1080,
            height: 1920,
        ));

        $manager->persist($this->buildVideo(
            uuid: self::VIDEO_PORTRAIT_720_UUID,
            folder: 'case_2_9x16_720',
            title: 'POC — Portrait 9:16 720p',
            duration: 39,
            width: 720,
            height: 1280,
        ));

        $manager->persist($this->buildVideo(
            uuid: self::VIDEO_SQUARE_1080_UUID,
            folder: 'case_3_1x1_1080',
            title: 'POC — Carré 1:1 1080p',
            duration: 61,
            width: 1080,
            height: 1080,
        ));

        $pending = new Video(Uuid::fromString(self::VIDEO_PENDING_UUID));
        $pending->title = 'POC — En attente de transcodage';
        $pending->status = VideoStatusEnum::PENDING;
        $manager->persist($pending);

        $manager->flush();
    }

    private function buildVideo(
        string $uuid,
        string $folder,
        string $title,
        int $duration,
        int $width,
        int $height,
    ): Video {
        $video = new Video(Uuid::fromString($uuid));
        $video->title = $title;
        $video->status = VideoStatusEnum::READY;
        $video->mediaPath = 'videos/'.$folder;
        $video->duration = $duration;
        $video->width = $width;
        $video->height = $height;

        return $video;
    }
}
