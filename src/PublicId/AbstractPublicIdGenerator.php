<?php

namespace App\PublicId;

abstract class AbstractPublicIdGenerator implements PublicIdGeneratorInterface
{
    private array $cachedIds = [];

    final public function generate(): string
    {
        $publicId = $this->build();

        if ($this->checkIfAlreadyExists($publicId)) {
            return $this->generate();
        }

        $this->cachedIds[] = $publicId;

        return $publicId;
    }

    final protected function generateRandomBlock(int $length): string
    {
        $block = '';

        for ($i = 0; $i < $length; ++$i) {
            $block .= random_int(0, 9);
        }

        return $block;
    }

    private function checkIfAlreadyExists(string $publicId): bool
    {
        return \in_array($publicId, $this->cachedIds, true) || $this->getRepository()->publicIdExists($publicId);
    }

    abstract protected function build(): string;

    abstract protected function getRepository(): PublicIdRepositoryInterface;
}
