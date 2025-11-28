<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Entity\Scope;
use App\Repository\ScopeRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\DataTransformerInterface;

class ScopeToCodeDataTransformer implements DataTransformerInterface
{
    public function __construct(private readonly ScopeRepository $scopeRepository)
    {
    }

    public function transform($scopeCodes): mixed
    {
        if ($scopeCodes) {
            return $this->scopeRepository->findBy(['code' => $scopeCodes]);
        }

        return $scopeCodes;
    }

    public function reverseTransform($scopes): mixed
    {
        if ($scopes) {
            $scopes = $scopes instanceof Collection ? $scopes->toArray() : $scopes;

            return array_map(fn (Scope $scope) => $scope->getCode(), $scopes);
        }

        return $scopes;
    }
}
