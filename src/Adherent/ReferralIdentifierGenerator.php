<?php

namespace App\Adherent;

use App\Repository\ReferralRepository;

class ReferralIdentifierGenerator
{
    private const CHARACTERS = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

    public function __construct(public readonly ReferralRepository $referralRepository)
    {
    }

    public function generate(): string
    {
        $identifier = self::build();

        return !$this->checkIfAlreadyExists($identifier)
            ? $identifier
            : $this->generate();
    }

    public static function build(): string
    {
        return 'P'.self::generateRandomBlock(5);
    }

    private static function generateRandomBlock(int $length): string
    {
        $block = '';

        for ($i = 0; $i < $length; ++$i) {
            $block .= self::CHARACTERS[random_int(0, \strlen(self::CHARACTERS) - 1)];
        }

        return $block;
    }

    private function checkIfAlreadyExists(string $identifier): bool
    {
        return $this->referralRepository->count(['identifier' => $identifier]) > 0;
    }
}
