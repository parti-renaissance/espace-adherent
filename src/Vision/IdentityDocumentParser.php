<?php

declare(strict_types=1);

namespace App\Vision;

use App\Entity\Adherent;
use Cocur\Slugify\SlugifyInterface;

class IdentityDocumentParser
{
    private const LEVENSHTEIN_LIMIT = 1;

    private const LAST_NAME_DEFAULT_LABEL = 'Nom';
    private const LAST_NAME_CUSTOM_LABELS = [
        'Epouse',
        'Veuve',
        'VV',
        "Nom d(\')?usage",
    ];

    private $slugify;

    public function __construct(SlugifyInterface $slugify)
    {
        $this->slugify = $slugify;
    }

    public function match(Adherent $adherent, ImageAnnotations $imageAnnotations): bool
    {
        return $this->hasFirstName($imageAnnotations, $adherent->getFirstName())
            && $this->hasLastName($imageAnnotations, $adherent->getLastName())
            && $this->hasDateOfBirth($imageAnnotations, $adherent->getBirthdate());
    }

    public function hasFirstName(ImageAnnotations $imageAnnotations, string $firstName): bool
    {
        if ($imageAnnotations->isFrenchNationalIdentityCard()) {
            preg_match('/Pr(?:e|é)nom[^:]{0,5}:?\s?(?<first_names>.+)\\n/', $imageAnnotations->getText(), $matches);

            $firstNames = array_map(function (string $firstName) {
                return $this->normalize($firstName);
            }, preg_split('/[\s,]+/', $matches['first_names'] ?? null));

            $normalizedFirstName = $this->normalize($firstName);

            foreach ($firstNames as $matchedFirstName) {
                if ($this->isMatching($matchedFirstName, $normalizedFirstName)) {
                    return true;
                }
            }

            return false;
        } elseif ($imageAnnotations->isFrenchPassport()) {
            $payload = $this->normalize($imageAnnotations->getText());

            return str_contains($payload, $this->normalize($firstName));
        }

        throw new \InvalidArgumentException(\sprintf('Instance of "%s" is not a handled identity document type.', ImageAnnotations::class));
    }

    public function hasLastName(ImageAnnotations $imageAnnotations, string $lastName): bool
    {
        if ($imageAnnotations->isFrenchNationalIdentityCard()) {
            $text = $imageAnnotations->getText();

            if (!$foundLastName = $this->retrieveCNILastName($text)) {
                return false;
            }

            $normalizedMatch = $this->normalize($foundLastName);
            $normalizedLastName = $this->normalize($lastName);

            return $this->isMatching($normalizedMatch, $normalizedLastName);
        } elseif ($imageAnnotations->isFrenchPassport()) {
            $payload = $this->normalize($imageAnnotations->getText());

            return str_contains($payload, $this->normalize($lastName));
        }

        throw new \InvalidArgumentException(\sprintf('Instance of "%s" is not a handled identity document type.', ImageAnnotations::class));
    }

    public function hasDateOfBirth(ImageAnnotations $imageAnnotations, \DateTimeInterface $dateOfBirth): bool
    {
        if ($imageAnnotations->isFrenchNationalIdentityCard()) {
            preg_match_all('/(?<birth_date>\d{2}[\. ]{1,2}\d{2}[\. ]{1,2}\d{4})/', $imageAnnotations->getText(), $matches);

            if (empty($matches['birth_date'])) {
                return false;
            }

            foreach ($matches['birth_date'] as $birthDate) {
                $birthDate = \DateTime::createFromFormat('d.m.Y', str_replace('..', '.', str_replace(' ', '.', $birthDate)));

                if ($birthDate->format('Y-m-d') === $dateOfBirth->format('Y-m-d')) {
                    return true;
                }
            }

            return false;
        } elseif ($imageAnnotations->isFrenchPassport()) {
            $payload = $this->normalize($imageAnnotations->getText());

            $pattern = \sprintf('/(?<birth_date>%s-?%s-?%s)/',
                $dateOfBirth->format('d'),
                $dateOfBirth->format('m'),
                $dateOfBirth->format('Y')
            );

            preg_match($pattern, str_replace(['o', 'l', 'i'], ['0', '1', '1'], $payload), $matches);

            return isset($matches['birth_date']) && $matches['birth_date'];
        }

        throw new \InvalidArgumentException(\sprintf('Instance of "%s" is not a handled identity document type.', ImageAnnotations::class));
    }

    private function normalize(?string $str): string
    {
        return $this->slugify->slugify(trim($str));
    }

    private function retrieveCNILastName(string $text): ?string
    {
        return $this->matchCNILastName(self::LAST_NAME_CUSTOM_LABELS, $text) ?? $this->matchCNILastName([self::LAST_NAME_DEFAULT_LABEL], $text);
    }

    private function matchCNILastName(array $labels, string $text): ?string
    {
        $pattern = '/(%s)\s?:?\s?(?<last_name>.+)\\n/';

        preg_match(\sprintf($pattern, implode('|', $labels)), $text, $matches);

        return $matches['last_name'] ?? null;
    }

    private function isMatching(string $str1, string $str2): bool
    {
        return $str1 === $str2 || self::LEVENSHTEIN_LIMIT >= levenshtein($str1, $str2);
    }
}
