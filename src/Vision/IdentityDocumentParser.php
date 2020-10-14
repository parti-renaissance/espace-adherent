<?php

namespace App\Vision;

use App\Entity\Adherent;
use Cocur\Slugify\Slugify;

class IdentityDocumentParser
{
    private $slugify;

    public function __construct(Slugify $slugify)
    {
        $this->slugify = $slugify;
    }

    public function match(Adherent $adherent, ImageAnnotations $imageAnnotations): bool
    {
        return $this->hasFirstName($imageAnnotations, $adherent->getFirstName())
            && $this->hasLastName($imageAnnotations, $adherent->getLastName())
            && $this->hasDateOfBirth($imageAnnotations, $adherent->getBirthdate())
        ;
    }

    public function hasFirstName(ImageAnnotations $imageAnnotations, string $firstName): bool
    {
        if ($imageAnnotations->isFrenchNationalIdentityCard()) {
            preg_match('/Prénom.{0,5}:\s?(?<first_names>.+)\\n/', $imageAnnotations->getText(), $matches);

            $firstNames = array_map(function (string $firstName) {
                return $this->normalize($firstName);
            }, preg_split('/[\s,]+/', $matches['first_names'] ?? null));

            return \in_array($this->normalize($firstName), $firstNames, true);
        } elseif ($imageAnnotations->isFrenchPassport()) {
            $payload = $this->normalize($imageAnnotations->getText());

            return false !== strpos($payload, $this->normalize($firstName));
        }

        throw new \InvalidArgumentException(sprintf('Instance of "%s" is not a handled identity document type.', ImageAnnotations::class));
    }

    public function hasLastName(ImageAnnotations $imageAnnotations, string $lastName): bool
    {
        if ($imageAnnotations->isFrenchNationalIdentityCard()) {
            preg_match('/Nom( )?:( )?(?<last_name>.+)\\n/', $imageAnnotations->getText(), $matches);

            return $this->normalize($matches['last_name'] ?? null) === $this->normalize($lastName);
        } elseif ($imageAnnotations->isFrenchPassport()) {
            $payload = $this->normalize($imageAnnotations->getText());

            return false !== strpos($payload, $this->normalize($lastName));
        }

        throw new \InvalidArgumentException(sprintf('Instance of "%s" is not a handled identity document type.', ImageAnnotations::class));
    }

    public function hasDateOfBirth(ImageAnnotations $imageAnnotations, \DateTimeInterface $dateOfBirth): bool
    {
        if ($imageAnnotations->isFrenchNationalIdentityCard()) {
            preg_match('/(?<birth_date>\d{2}[\. ]{1,2}\d{2}[\. ]{1,2}\d{4})/', $imageAnnotations->getText(), $matches);

            if (!isset($matches['birth_date']) || !$matches['birth_date']) {
                return false;
            }

            $birthDate = \DateTime::createFromFormat('d.m.Y', str_replace('..', '.', str_replace(' ', '.', $matches['birth_date'])));

            return $birthDate->format('Y-m-d') === $dateOfBirth->format('Y-m-d');
        } elseif ($imageAnnotations->isFrenchPassport()) {
            $payload = $this->normalize($imageAnnotations->getText());

            $pattern = sprintf('/(?<birth_date>%s-?%s-?%s)/',
                $dateOfBirth->format('d'),
                $dateOfBirth->format('m'),
                $dateOfBirth->format('Y')
            );

            preg_match($pattern, str_replace(['o', 'l', 'i'], ['0', '1', '1'], $payload), $matches);

            return isset($matches['birth_date']) && $matches['birth_date'];
        }

        throw new \InvalidArgumentException(sprintf('Instance of "%s" is not a handled identity document type.', ImageAnnotations::class));
    }

    private function normalize(?string $str): string
    {
        return $this->slugify->slugify(trim($str));
    }
}
