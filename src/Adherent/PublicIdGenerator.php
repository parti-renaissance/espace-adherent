<?php

namespace App\Adherent;

use App\Repository\AdherentRepository;

class PublicIdGenerator
{
    private array $cachedIds = [];

    public function __construct(public readonly AdherentRepository $adherentRepository)
    {
    }

    public function generate(): string
    {
        $publicId = self::build();

        if ($this->checkIfAlreadyExists($publicId)) {
            return $this->generate();
        }

        $this->cachedIds[] = $publicId;

        return $publicId;
    }

    public static function build(): string
    {
        return self::generateRandomBlock(3).'-'.self::generateRandomBlock(3);
    }

    private static function generateRandomBlock(int $length): string
    {
        $block = '';

        for ($i = 0; $i < $length; ++$i) {
            $block .= random_int(0, 9);
        }

        return $block;
    }

    private function checkIfAlreadyExists(string $publicId): bool
    {
        return \in_array($publicId, $this->cachedIds, true)
            || $this->adherentRepository->count(['publicId' => $publicId]) > 0;
    }
}
