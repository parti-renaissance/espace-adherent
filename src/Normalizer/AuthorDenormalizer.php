<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Adherent;
use App\Entity\AuthorInstanceInterface;
use App\Entity\AuthorInterface;
use App\Entity\Event\Event;
use App\OAuth\Model\Scope as OAuthScope;
use App\Scope\Exception\ScopeExceptionInterface;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class AuthorDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public function __construct(
        private readonly Security $security,
        private readonly ScopeGeneratorResolver $scopeGeneratorResolver,
        private readonly GeneralScopeGenerator $generalScopeGenerator,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function denormalize($data, $class, $format = null, array $context = []): mixed
    {
        /** @var AuthorInterface $data */
        $data = $this->denormalizer->denormalize($data, $class, $format, $context + [__CLASS__ => true]);

        if (!$data->getId()) {
            $scope = $this->scopeGeneratorResolver->generate();
            $data->setAuthor($scope ? $scope->getMainUser() : $this->security->getUser());
        }

        if ($data instanceof AuthorInstanceInterface && !$data->getAuthorInstance()) {
            $scope = $this->scopeGeneratorResolver->generate();

            if (!$scope && $data instanceof Event && ($author = $data->getAuthor()) instanceof Adherent) {
                $scope = $this->resolveMilitantScope($author);
            }

            if ($scope) {
                $data->updateFromScope($scope);
            }
        }

        return $data;
    }

    private function resolveMilitantScope(Adherent $author): ?Scope
    {
        if (!$this->authorizationChecker->isGranted(OAuthScope::generateRole(OAuthScope::JEMARCHE_APP))) {
            return null;
        }

        try {
            return $this->generalScopeGenerator->getGenerator(ScopeEnum::MILITANT, $author)->generate($author);
        } catch (ScopeExceptionInterface) {
            return null;
        }
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            AuthorInterface::class => false,
        ];
    }

    public function supportsDenormalization($data, $type, $format = null, array $context = []): bool
    {
        return !isset($context[__CLASS__])
            && is_a($type, AuthorInterface::class, true)
            && $this->security->getUser() instanceof Adherent;
    }
}
