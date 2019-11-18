<?php

namespace AppBundle\Normalizer;

use AppBundle\Entity\Mooc\AttachmentFile;
use AppBundle\Entity\Mooc\AttachmentLink;
use AppBundle\Entity\Mooc\BaseMoocElement;
use AppBundle\Entity\Mooc\Chapter;
use AppBundle\Entity\Mooc\Mooc;
use AppBundle\Entity\Mooc\MoocElementTypeEnum;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MoocNormalizer implements NormalizerInterface
{
    private const FORMAT = 'json';

    /** @var RouterInterface */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function normalize($object, $format = null, array $context = [])
    {
        $elements = [];

        /** @var Mooc $object */
        foreach ($object->getChapters() as $chapter) {
            $elements[] = $this->normalizeChapter($chapter);

            foreach ($chapter->getElements() as $element) {
                if ($chapter->isPublished()) {
                    $elements[] = $this->normalizeElement($element);
                }
            }
        }

        return $this->normalizeMooc($object, $elements);
    }

    public function supportsNormalization($data, $format = null)
    {
        return self::FORMAT === $format && $data instanceof Mooc;
    }

    private function normalizeMooc(Mooc $mooc, array $elements): array
    {
        return [
            'title' => $mooc->getTitle(),
            'slug' => $mooc->getSlug(),
            'content' => $mooc->getContent(),
            'youtubeId' => $mooc->getYoutubeId(),
            'youtubeThumbnail' => $mooc->getYoutubeThumbnail(),
            'articleImage' => ($image = $mooc->getArticleImage()) ? $this->router->generate('asset_url', ['path' => $image->getFilePath()], UrlGeneratorInterface::ABSOLUTE_URL) : null,
            'youtubeDuration' => $mooc->getYoutubeDuration() ? $mooc->getYoutubeDuration()->format('H:i:s') : null,
            'shareTwitterText' => $mooc->getShareTwitterText(),
            'shareFacebookText' => $mooc->getShareFacebookText(),
            'shareEmailSubject' => $mooc->getShareEmailSubject(),
            'shareEmailBody' => $mooc->getShareEmailBody(),
            'elements' => $elements,
        ];
    }

    private function normalizeChapter(Chapter $chapter): array
    {
        return [
            'type' => 'chapter',
            'title' => $chapter->getTitle(),
            'slug' => $chapter->getSlug(),
            'publishedAt' => $chapter->getPublishedAt()->format('Y-m-d H:i:s'),
        ];
    }

    private function normalizeElement(BaseMoocElement $element): array
    {
        $moocElement = [
            'type' => $element->getType(),
            'title' => $element->getTitle(),
            'slug' => $element->getSlug(),
            'content' => $element->getContent(),
            'shareTwitterText' => $element->getShareTwitterText(),
            'shareFacebookText' => $element->getShareFacebookText(),
            'shareEmailSubject' => $element->getShareEmailSubject(),
            'shareEmailBody' => $element->getShareEmailBody(),
            'links' => $this->normalizeLinks($element->getLinks()),
            'attachments' => $this->normalizeFiles($element->getFiles()),
        ];

        switch ($element->getType()) {
            case MoocElementTypeEnum::VIDEO:
                $moocElement['youtubeId'] = $element->getYoutubeId();
                $moocElement['youtubeThumbnail'] = $element->getYoutubeThumbnail();
                $moocElement['duration'] = $element->getDuration()->format('H:i:s');
                break;
            case MoocElementTypeEnum::IMAGE:
                $moocElement['image'] = $this->router->generate(
                    'asset_url',
                    ['path' => $element->getImage()->getFilePath()],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                break;
            case MoocElementTypeEnum::QUIZ:
                $moocElement['typeformUrl'] = $element->getTypeformUrl();
                break;
        }

        return $moocElement;
    }

    private function normalizeLinks(Collection $links): array
    {
        $attachmentLinks = [];

        /** @var AttachmentLink $link */
        foreach ($links as $link) {
            $attachmentLinks[] = [
                'linkName' => $link->getTitle(),
                'linkUrl' => $link->getLink(),
            ];
        }

        return $attachmentLinks;
    }

    private function normalizeFiles(Collection $files): array
    {
        $attachmentFiles = [];

        /** @var AttachmentFile $file */
        foreach ($files as $file) {
            $attachmentFiles[] = [
                'attachmentName' => $file->getTitle(),
                'attachmentUrl' => $this->router->generate(
                    'mooc_get_file',
                    [
                        'slug' => $file->getSlug(),
                        'extension' => $file->getExtension(),
                    ],
                    UrlGenerator::ABSOLUTE_URL
                ),
            ];
        }

        return $attachmentFiles;
    }
}
