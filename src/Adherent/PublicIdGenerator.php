<?php

namespace App\Adherent;

use App\Repository\AdherentRepository;

class PublicIdGenerator
{
    public function __construct(public readonly AdherentRepository $adherentRepository)
    {
    }

    public function generate(): string
    {
        $publicId = self::build();

        return !$this->checkIfAlreadyExists($publicId)
            ? $publicId
            : $this->generate();
    }

    public static function build(): string
    {
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        $charactersArray = str_split($characters);

        shuffle($charactersArray);

        $block1 = self::generateRandomBlock($charactersArray, 3);
        $block2 = self::generateRandomBlock($charactersArray, 3);

        return $block1.'-'.$block2;
    }

    private static function generateRandomBlock(array $characters, int $length): string
    {
        $block = '';
        $maxIndex = \count($characters) - 1;

        for ($i = 0; $i < $length; ++$i) {
            $block .= $characters[random_int(0, $maxIndex)];
        }

        return $block;
    }

    private function checkIfAlreadyExists(string $publicId): bool
    {
        return $this->adherentRepository->count(['publicId' => $publicId]) > 0;
    }
}
