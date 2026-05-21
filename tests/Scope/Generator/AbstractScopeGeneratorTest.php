<?php

declare(strict_types=1);

namespace Tests\App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\Scope as ScopeEntity;
use App\Repository\ScopeRepository;
use App\Scope\FeatureEnum;
use App\Scope\Generator\AbstractScopeGenerator;
use App\Scope\Generator\MilitantScopeGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class AbstractScopeGeneratorTest extends TestCase
{
    public function testDelegatedAccessExcludesDelegatorDisabledFeatures(): void
    {
        $delegator = $this->createStub(Adherent::class);
        $delegator->disabledScopeFeatures = [FeatureEnum::EVENTS];

        $delegatee = $this->createStub(Adherent::class);
        $delegatee->disabledScopeFeatures = [];

        $features = $this->resolveDelegatedFeatures($delegator, $delegatee);

        self::assertNotContains(FeatureEnum::EVENTS, $features, 'A feature disabled for the delegator must not be granted through delegation.');
        self::assertContains(FeatureEnum::CONTACTS, $features);
    }

    public function testDelegatedAccessExcludesDelegateeDisabledFeatures(): void
    {
        $delegator = $this->createStub(Adherent::class);
        $delegator->disabledScopeFeatures = [];

        $delegatee = $this->createStub(Adherent::class);
        $delegatee->disabledScopeFeatures = [FeatureEnum::CONTACTS];

        $features = $this->resolveDelegatedFeatures($delegator, $delegatee);

        self::assertNotContains(FeatureEnum::CONTACTS, $features);
        self::assertContains(FeatureEnum::EVENTS, $features);
    }

    /**
     * Invokes the (private) feature resolution of a delegated scope for the given delegator/delegatee.
     */
    private function resolveDelegatedFeatures(Adherent $delegator, Adherent $delegatee): array
    {
        $delegatedAccess = $this->createStub(DelegatedAccess::class);
        $delegatedAccess->method('getDelegator')->willReturn($delegator);
        $delegatedAccess->method('getScopeFeatures')->willReturn([FeatureEnum::EVENTS, FeatureEnum::CONTACTS]);

        $generator = new MilitantScopeGenerator(
            $this->createStub(ScopeRepository::class),
            $this->createStub(TranslatorInterface::class),
        );
        $generator->setDelegatedAccess($delegatedAccess);

        $scopeEntity = new ScopeEntity('deputy', 'Deputy', [FeatureEnum::EVENTS, FeatureEnum::CONTACTS, FeatureEnum::DASHBOARD], []);

        return new \ReflectionMethod(AbstractScopeGenerator::class, 'getFeatures')->invoke($generator, $scopeEntity, $delegatee);
    }
}
