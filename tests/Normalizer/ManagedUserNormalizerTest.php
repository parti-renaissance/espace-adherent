<?php

declare(strict_types=1);

namespace Tests\App\Normalizer;

use App\Adherent\Tag\TagTranslator;
use App\Api\Serializer\ManagedUserContextBuilder;
use App\Entity\Projection\ManagedUser;
use App\Entity\SubscriptionType;
use App\Normalizer\ManagedUserNormalizer;
use App\Repository\SubscriptionTypeRepository;
use App\Scope\ScopeGeneratorResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ManagedUserNormalizerTest extends TestCase
{
    private TranslatorInterface&MockObject $translator;
    private TagTranslator&MockObject $tagTranslator;
    private ScopeGeneratorResolver&MockObject $scopeGeneratorResolver;
    private SubscriptionTypeRepository&MockObject $subscriptionTypeRepository;
    private NormalizerInterface&MockObject $innerNormalizer;
    private ManagedUserNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->tagTranslator = $this->createMock(TagTranslator::class);
        $this->scopeGeneratorResolver = $this->createMock(ScopeGeneratorResolver::class);
        $this->subscriptionTypeRepository = $this->createMock(SubscriptionTypeRepository::class);
        $this->innerNormalizer = $this->createMock(NormalizerInterface::class);

        $this->normalizer = new ManagedUserNormalizer(
            $this->translator,
            $this->tagTranslator,
            $this->scopeGeneratorResolver,
            $this->subscriptionTypeRepository,
        );
        $this->normalizer->setNormalizer($this->innerNormalizer);
    }

    public function testSupportsNormalizationReturnsTrueForManagedUser(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);

        self::assertTrue($this->normalizer->supportsNormalization($managedUser));
    }

    public function testSupportsNormalizationReturnsFalseForNonManagedUser(): void
    {
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testSupportsNormalizationReturnsFalseWhenAlreadyProcessed(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);

        self::assertFalse($this->normalizer->supportsNormalization(
            $managedUser,
            null,
            [ManagedUserNormalizer::class => true]
        ));
    }

    public function testNormalizeAddsSubscriptionTypesAndRolesForVoxDetailGroup(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('getRoles')
            ->willReturn([
                ['code' => 'president_departmental_assembly', 'zones' => 'Paris', 'zone_codes' => '75'],
                ['code' => 'animator', 'is_delegated' => true, 'function' => 'Communication'],
            ])
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('male')
        ;
        $managedUser
            ->method('getSubscriptionTypes')
            ->willReturn(['subscribed_emails_movement_information', 'militant_action_sms'])
        ;

        $this->translator
            ->method('trans')
            ->willReturnMap([
                ['role.president_departmental_assembly', ['gender' => 'male'], null, null, "Président d'Assemblée Départementale"],
                ['role.animator', ['gender' => 'male'], null, null, 'Animateur'],
            ])
        ;

        // Mock subscription types from repository
        $subscriptionType1 = $this->createMock(SubscriptionType::class);
        $subscriptionType1->method('getCode')->willReturn('subscribed_emails_movement_information');
        $subscriptionType1->method('getLabel')->willReturn('National');

        $subscriptionType2 = $this->createMock(SubscriptionType::class);
        $subscriptionType2->method('getCode')->willReturn('subscribed_emails_weekly_letter');
        $subscriptionType2->method('getLabel')->willReturn('Recevoir la newsletter hebdomadaire');

        $subscriptionType3 = $this->createMock(SubscriptionType::class);
        $subscriptionType3->method('getCode')->willReturn('militant_action_sms');
        $subscriptionType3->method('getLabel')->willReturn('Recevoir les SMS militants');

        $this->subscriptionTypeRepository
            ->method('findAllOrderedByPosition')
            ->willReturn([$subscriptionType1, $subscriptionType2, $subscriptionType3])
        ;

        $context = [
            'groups' => [ManagedUserContextBuilder::GROUP_VOX, ManagedUserContextBuilder::GROUP_VOX_DETAIL],
        ];

        $this->innerNormalizer
            ->method('normalize')
            ->willReturn([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'first_name' => 'John',
                'last_name' => 'Doe',
            ])
        ;

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertIsArray($result);
        self::assertArrayHasKey('subscription_types', $result);
        self::assertCount(3, $result['subscription_types']);

        self::assertSame([
            'code' => 'subscribed_emails_movement_information',
            'label' => 'National',
            'subscribed' => true,
        ], $result['subscription_types'][0]);

        self::assertSame([
            'code' => 'subscribed_emails_weekly_letter',
            'label' => 'Recevoir la newsletter hebdomadaire',
            'subscribed' => false,
        ], $result['subscription_types'][1]);

        self::assertSame([
            'code' => 'militant_action_sms',
            'label' => 'Recevoir les SMS militants',
            'subscribed' => true,
        ], $result['subscription_types'][2]);

        self::assertArrayHasKey('roles', $result);
        self::assertCount(2, $result['roles']);

        self::assertSame([
            'code' => 'president_departmental_assembly',
            'label' => "Président d'Assemblée Départementale",
            'is_delegated' => false,
            'function' => null,
            'zones' => 'Paris',
            'zone_codes' => '75',
        ], $result['roles'][0]);

        self::assertSame([
            'code' => 'animator',
            'label' => 'Animateur',
            'is_delegated' => true,
            'function' => 'Communication',
            'zones' => null,
            'zone_codes' => null,
        ], $result['roles'][1]);
    }

    public function testNormalizeAddsRolesButNotSubscriptionTypesForVoxListOnly(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('getRoles')
            ->willReturn([['code' => 'animator']])
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('male')
        ;

        $context = [
            'groups' => [ManagedUserContextBuilder::GROUP_VOX],
        ];

        $this->innerNormalizer
            ->method('normalize')
            ->willReturn(['uuid' => '123e4567-e89b-12d3-a456-426614174000'])
        ;

        $this->translator
            ->method('trans')
            ->with('role.animator', ['gender' => 'male'])
            ->willReturn('Animateur')
        ;

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayNotHasKey('subscription_types', $result);
        self::assertArrayHasKey('roles', $result);
        self::assertCount(1, $result['roles']);
        self::assertSame('animator', $result['roles'][0]['code']);
    }

    public function testNormalizeSkipsEmptyRoleCodes(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('getRoles')
            ->willReturn([
                ['code' => 'animator'],
                ['code' => ''],
                ['code' => null],
                [],
            ])
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('male')
        ;

        $context = [
            'groups' => [ManagedUserContextBuilder::GROUP_VOX],
        ];

        $this->innerNormalizer
            ->method('normalize')
            ->willReturn(['uuid' => '123e4567-e89b-12d3-a456-426614174000'])
        ;

        $this->translator
            ->method('trans')
            ->with('role.animator', ['gender' => 'male'])
            ->willReturn('Animateur')
        ;

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('roles', $result);
        self::assertCount(1, $result['roles']);
        self::assertSame('animator', $result['roles'][0]['code']);
    }

    public function testNormalizeTransformsRolesToTagsForNonVoxGroups(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('isEmailSubscribed')
            ->willReturn(true)
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('female')
        ;

        $this->scopeGeneratorResolver
            ->method('generate')
            ->willReturn(null)
        ;

        $context = [
            'groups' => ['managed_users_list'],
        ];

        $this->innerNormalizer
            ->method('normalize')
            ->willReturn([
                'uuid' => '123e4567-e89b-12d3-a456-426614174000',
                'tags' => [],
                'roles' => [
                    ['code' => 'animator'],
                ],
            ])
        ;

        $this->translator
            ->method('trans')
            ->with('role.animator', ['gender' => 'female'])
            ->willReturn('Animatrice')
        ;

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayNotHasKey('roles', $result);
        self::assertArrayHasKey('tags', $result);
        self::assertCount(1, $result['tags']);
        self::assertSame('role', $result['tags'][0]['type']);
        self::assertSame('Animatrice', $result['tags'][0]['label']);
    }

    public function testGetSupportedTypesReturnsManagedUserClass(): void
    {
        $types = $this->normalizer->getSupportedTypes(null);

        self::assertArrayHasKey(ManagedUser::class, $types);
        self::assertFalse($types[ManagedUser::class]);
    }
}
