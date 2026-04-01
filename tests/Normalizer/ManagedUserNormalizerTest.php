<?php

declare(strict_types=1);

namespace Tests\App\Normalizer;

use App\Adherent\MandateTypeEnum;
use App\Adherent\Tag\TagTranslator;
use App\Api\Serializer\ManagedUserContextBuilder;
use App\Entity\Projection\ManagedUser;
use App\Entity\SubscriptionType;
use App\Normalizer\ManagedUserNormalizer;
use App\Repository\SubscriptionTypeRepository;
use App\Scope\ScopeEnum;
use App\Scope\ScopeGeneratorResolver;
use PHPUnit\Framework\Attributes\DataProvider;
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
                ['code' => 'president_departmental_assembly', 'zones' => '75', 'zone_codes' => '75'],
                ['code' => 'animator', 'is_delegated' => true, 'function' => 'Communication', 'zones' => '92', 'zone_codes' => '92'],
            ])
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('male')
        ;
        $managedUser
            ->method('getElectMandates')
            ->willReturn(null)
        ;
        $managedUser
            ->method('getSubscriptionTypes')
            ->willReturn(['subscribed_emails_movement_information', 'militant_action_sms'])
        ;
        $managedUser
            ->method('getCommittee')
            ->willReturn(null)
        ;
        $managedUser
            ->method('getAgora')
            ->willReturn(null)
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

        // Role with zones: label should include zone display (code for non-region)
        self::assertSame([
            'code' => 'president_departmental_assembly',
            'label' => "Président d'Assemblée Départementale (75)",
            'is_delegated' => false,
            'function' => null,
            'zones' => '75',
            'zone_codes' => '75',
        ], $result['roles'][0]);

        // Delegated role: label should be "{function} ({zone_display})"
        self::assertSame([
            'code' => 'animator',
            'label' => 'Communication (92)',
            'is_delegated' => true,
            'function' => 'Communication',
            'zones' => '92',
            'zone_codes' => '92',
        ], $result['roles'][1]);
    }

    public function testNormalizeAddsRolesButNotSubscriptionTypesForVoxListOnly(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('getRoles')
            ->willReturn([['code' => 'animator', 'zones' => 'Comité Levallois']])
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('male')
        ;
        $managedUser
            ->method('getElectMandates')
            ->willReturn(null)
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
        self::assertArrayHasKey('elect_mandates', $result);
        self::assertSame([], $result['elect_mandates']);
        self::assertCount(1, $result['roles']);
        self::assertSame('animator', $result['roles'][0]['code']);
        // Animator with committee: label should include committee name
        self::assertSame('Animateur (Comité Levallois)', $result['roles'][0]['label']);
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
        $managedUser
            ->method('getElectMandates')
            ->willReturn(null)
        ;
        $managedUser
            ->method('getCommittee')
            ->willReturn(null)
        ;
        $managedUser
            ->method('getAgora')
            ->willReturn(null)
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
                    ['code' => 'animator', 'zones' => 'Comité Paris'],
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
        // Animator with committee: label should include committee name from zones
        self::assertSame('Animatrice (Comité Paris)', $result['tags'][0]['label']);
    }

    public function testNormalizeTransformsElectMandatesToCodeLabelForVoxGroup(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('getRoles')
            ->willReturn([])
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('male')
        ;
        $managedUser
            ->method('getElectMandates')
            ->willReturn(['conseiller_municipal', 'depute'])
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
            ->willReturnMap([
                ['adherent.mandate.type.conseiller_municipal', [], null, null, 'Conseiller municipal'],
                ['adherent.mandate.type.depute', [], null, null, 'Député'],
            ])
        ;

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('elect_mandates', $result);
        self::assertCount(2, $result['elect_mandates']);

        // Mandates are sorted by MandateTypeEnum::ALL order (national to local)
        // depute comes before conseiller_municipal
        self::assertSame([
            'code' => 'depute',
            'label' => 'Député',
        ], $result['elect_mandates'][0]);

        self::assertSame([
            'code' => 'conseiller_municipal',
            'label' => 'Conseiller municipal',
        ], $result['elect_mandates'][1]);
    }

    public function testGetSupportedTypesReturnsManagedUserClass(): void
    {
        $types = $this->normalizer->getSupportedTypes(null);

        self::assertArrayHasKey(ManagedUser::class, $types);
        self::assertFalse($types[ManagedUser::class]);
    }

    #[DataProvider('provideFormatRoleLabelCases')]
    public function testFormatRoleLabelVox(
        array $role,
        ?string $committee,
        ?string $agora,
        string $expectedLabel,
    ): void {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('getRoles')
            ->willReturn([$role])
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('male')
        ;
        $managedUser
            ->method('getElectMandates')
            ->willReturn(null)
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
            ->willReturnCallback(function (string $key, array $params) {
                return match ($key) {
                    'role.national' => 'Secrétaire Général de Renaissance',
                    'role.animator' => 'Animateur',
                    'role.agora_president' => 'Président d\'Agora',
                    'role.agora_general_secretary' => 'Secrétaire Général d\'Agora',
                    'role.president_departmental_assembly' => 'Président d\'Assemblée Départementale',
                    'role.regional_delegate' => 'Délégué Régional',
                    default => $key,
                };
            })
        ;

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('roles', $result);
        self::assertCount(1, $result['roles']);
        self::assertSame($expectedLabel, $result['roles'][0]['label']);
    }

    public static function provideFormatRoleLabelCases(): iterable
    {
        // Priority 1: Delegated role with zone (department = code)
        yield 'delegated_with_zone' => [
            'role' => [
                'code' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'is_delegated' => true,
                'function' => 'Secrétaire Général',
                'zones' => '92',
                'zone_codes' => '92',
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Secrétaire Général (92)',
        ];

        // Priority 1: Delegated role without zone
        yield 'delegated_without_zone' => [
            'role' => [
                'code' => ScopeEnum::ANIMATOR,
                'is_delegated' => true,
                'function' => 'Communication',
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Communication',
        ];

        // Priority 2: National role (no zone displayed)
        yield 'national_scope' => [
            'role' => [
                'code' => ScopeEnum::NATIONAL,
                'zone_codes' => 'FR',  // Should be ignored for national
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Secrétaire Général de Renaissance',
        ];

        // Priority 3: Animator with committee (name from zones)
        yield 'animator_with_committee' => [
            'role' => [
                'code' => ScopeEnum::ANIMATOR,
                'zones' => 'Comité Levallois',
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Animateur (Comité Levallois)',
        ];

        // Priority 3: Animator without committee = fallback
        yield 'animator_without_committee' => [
            'role' => [
                'code' => ScopeEnum::ANIMATOR,
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Animateur',
        ];

        // Priority 4: Agora president (name from zones)
        yield 'agora_president' => [
            'role' => [
                'code' => ScopeEnum::AGORA_PRESIDENT,
                'zones' => 'Laïcité',
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Président d\'Agora (Laïcité)',
        ];

        // Priority 4: Agora general secretary (name from zones)
        yield 'agora_general_secretary' => [
            'role' => [
                'code' => ScopeEnum::AGORA_GENERAL_SECRETARY,
                'zones' => 'Europe',
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Secrétaire Général d\'Agora (Europe)',
        ];

        // Priority 4: Agora without name = fallback
        yield 'agora_without_name' => [
            'role' => [
                'code' => ScopeEnum::AGORA_PRESIDENT,
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Président d\'Agora',
        ];

        // Priority 5: Role with zone (department = code)
        yield 'role_with_zone' => [
            'role' => [
                'code' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                'zones' => '75',
                'zone_codes' => '75',
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Président d\'Assemblée Départementale (75)',
        ];

        // Priority 5: Role with region zone (region = name from worker)
        yield 'role_with_region_zone' => [
            'role' => [
                'code' => ScopeEnum::REGIONAL_DELEGATE,
                'zones' => 'Île-de-France',
                'zone_codes' => 'IDF',
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Délégué Régional (Île-de-France)',
        ];

        // Priority 6: Fallback (no zone or context)
        yield 'fallback_no_context' => [
            'role' => [
                'code' => ScopeEnum::REGIONAL_DELEGATE,
            ],
            'committee' => null,
            'agora' => null,
            'expectedLabel' => 'Délégué Régional',
        ];
    }

    public function testNormalizeTagsForDelegatedRoleWithZone(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('isEmailSubscribed')
            ->willReturn(true)
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('male')
        ;
        $managedUser
            ->method('getCommittee')
            ->willReturn(null)
        ;
        $managedUser
            ->method('getAgora')
            ->willReturn(null)
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
                    [
                        'code' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                        'is_delegated' => true,
                        'function' => 'Secrétaire Général',
                        'zones' => '92',
                        'zone_codes' => '92',
                    ],
                ],
            ])
        ;

        $this->translator
            ->method('trans')
            ->willReturn("Président d'Assemblée Départementale")
        ;

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayNotHasKey('roles', $result);
        self::assertArrayHasKey('tags', $result);
        self::assertCount(1, $result['tags']);
        self::assertSame('role', $result['tags'][0]['type']);
        // Delegated role with zone: "{function} ({zone_display})"
        self::assertSame('Secrétaire Général (92)', $result['tags'][0]['label']);
        self::assertSame('Secrétaire Général', $result['tags'][0]['tooltip']);
    }

    public function testNormalizeTagsForDelegatedRoleWithZoneFemale(): void
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
        $managedUser
            ->method('getCommittee')
            ->willReturn(null)
        ;
        $managedUser
            ->method('getAgora')
            ->willReturn(null)
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
                    [
                        'code' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                        'is_delegated' => true,
                        'function' => 'Secrétaire Générale',
                        'zones' => '75',
                        'zone_codes' => '75',
                    ],
                ],
            ])
        ;

        $this->translator
            ->method('trans')
            ->willReturn("Présidente d'Assemblée Départementale")
        ;

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('tags', $result);
        self::assertCount(1, $result['tags']);
        // Delegated female: department zone = code
        self::assertSame('Secrétaire Générale (75)', $result['tags'][0]['label']);
    }

    public function testNormalizeTagsForRoleWithZone(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('isEmailSubscribed')
            ->willReturn(true)
        ;
        $managedUser
            ->method('getGender')
            ->willReturn('male')
        ;
        $managedUser
            ->method('getCommittee')
            ->willReturn(null)
        ;
        $managedUser
            ->method('getAgora')
            ->willReturn(null)
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
                    [
                        'code' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY,
                        'zones' => '92, 93',
                        'zone_codes' => '92, 93',
                    ],
                ],
            ])
        ;

        $this->translator
            ->method('trans')
            ->with('role.president_departmental_assembly', ['gender' => 'male'])
            ->willReturn("Président d'Assemblée Départementale")
        ;

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('tags', $result);
        self::assertCount(1, $result['tags']);
        self::assertSame('role', $result['tags'][0]['type']);
        // Role with department zones: codes
        self::assertSame("Président d'Assemblée Départementale (92, 93)", $result['tags'][0]['label']);
    }

    public function testSortRolesByPriorityPriorityDirectThenOtherDirectThenDelegated(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('getRoles')
            ->willReturn([
                ['code' => ScopeEnum::DEPUTY, 'is_delegated' => false],                      // non-priority direct
                ['code' => ScopeEnum::ANIMATOR, 'is_delegated' => true, 'function' => 'F1'], // priority delegated
                ['code' => ScopeEnum::LEGISLATIVE_CANDIDATE, 'is_delegated' => false],       // priority direct (index 0)
                ['code' => ScopeEnum::SENATOR, 'is_delegated' => true, 'function' => 'F2'],  // non-priority delegated
                ['code' => ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, 'is_delegated' => false], // priority direct (index 2)
            ])
        ;
        $managedUser->method('getGender')->willReturn('male');
        $managedUser->method('getElectMandates')->willReturn(null);
        $managedUser->method('getCommittee')->willReturn(null);
        $managedUser->method('getAgora')->willReturn(null);

        $context = ['groups' => [ManagedUserContextBuilder::GROUP_VOX]];

        $this->innerNormalizer->method('normalize')->willReturn(['uuid' => 'test']);
        $this->translator->method('trans')->willReturnCallback(function (string $key) {
            return $key;
        });

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('roles', $result);
        self::assertCount(5, $result['roles']);

        // Expected order (ScopeEnum::ALL defines priority):
        // 1. Direct roles by ScopeEnum::ALL: legislative_candidate, president_departmental_assembly, deputy
        // 2. Delegated roles by ScopeEnum::ALL: animator, senator
        self::assertSame(ScopeEnum::LEGISLATIVE_CANDIDATE, $result['roles'][0]['code']);
        self::assertSame(ScopeEnum::PRESIDENT_DEPARTMENTAL_ASSEMBLY, $result['roles'][1]['code']);
        self::assertSame(ScopeEnum::DEPUTY, $result['roles'][2]['code']);
        self::assertSame(ScopeEnum::ANIMATOR, $result['roles'][3]['code']);
        self::assertSame(ScopeEnum::SENATOR, $result['roles'][4]['code']);
    }

    public function testSortRolesByPriorityUnknownRoleGoesAtEndOfCategory(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser
            ->method('getRoles')
            ->willReturn([
                ['code' => 'unknown_role', 'is_delegated' => false],           // unknown direct
                ['code' => ScopeEnum::DEPUTY, 'is_delegated' => false],        // known direct
                ['code' => 'another_unknown', 'is_delegated' => true, 'function' => 'F'], // unknown delegated
            ])
        ;
        $managedUser->method('getGender')->willReturn('male');
        $managedUser->method('getElectMandates')->willReturn(null);
        $managedUser->method('getCommittee')->willReturn(null);
        $managedUser->method('getAgora')->willReturn(null);

        $context = ['groups' => [ManagedUserContextBuilder::GROUP_VOX]];

        $this->innerNormalizer->method('normalize')->willReturn(['uuid' => 'test']);
        $this->translator->method('trans')->willReturnCallback(function (string $key) {
            return $key;
        });

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('roles', $result);
        self::assertCount(3, $result['roles']);

        // Direct roles first (deputy known, then unknown), then delegated
        self::assertSame(ScopeEnum::DEPUTY, $result['roles'][0]['code']);
        self::assertSame('unknown_role', $result['roles'][1]['code']);
        self::assertSame('another_unknown', $result['roles'][2]['code']);
    }

    public function testSortRolesByPriorityEmptyArrayReturnsEmpty(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser->method('getRoles')->willReturn([]);
        $managedUser->method('getGender')->willReturn('male');
        $managedUser->method('getElectMandates')->willReturn(null);
        $managedUser->method('getCommittee')->willReturn(null);
        $managedUser->method('getAgora')->willReturn(null);

        $context = ['groups' => [ManagedUserContextBuilder::GROUP_VOX]];

        $this->innerNormalizer->method('normalize')->willReturn(['uuid' => 'test']);

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('roles', $result);
        self::assertSame([], $result['roles']);
    }

    public function testSortMandatesByPriorityNationalToLocalOrder(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser->method('getRoles')->willReturn([]);
        $managedUser->method('getGender')->willReturn('male');
        // Out of order: local then national
        $managedUser
            ->method('getElectMandates')
            ->willReturn([
                MandateTypeEnum::CONSEILLER_MUNICIPAL,    // local
                MandateTypeEnum::DEPUTE,                  // national
                MandateTypeEnum::MAIRE,                   // local
                MandateTypeEnum::SENATEUR,                // national
            ])
        ;
        $managedUser->method('getCommittee')->willReturn(null);
        $managedUser->method('getAgora')->willReturn(null);

        $context = ['groups' => [ManagedUserContextBuilder::GROUP_VOX]];

        $this->innerNormalizer->method('normalize')->willReturn(['uuid' => 'test']);
        $this->translator->method('trans')->willReturnCallback(function (string $key) {
            return $key;
        });

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('elect_mandates', $result);
        self::assertCount(4, $result['elect_mandates']);

        // Expected order per MandateTypeEnum::ALL: senateur, depute, maire, conseiller_municipal
        self::assertSame(MandateTypeEnum::SENATEUR, $result['elect_mandates'][0]['code']);
        self::assertSame(MandateTypeEnum::DEPUTE, $result['elect_mandates'][1]['code']);
        self::assertSame(MandateTypeEnum::MAIRE, $result['elect_mandates'][2]['code']);
        self::assertSame(MandateTypeEnum::CONSEILLER_MUNICIPAL, $result['elect_mandates'][3]['code']);
    }

    public function testSortMandatesByPriorityEmptyArrayReturnsEmpty(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser->method('getRoles')->willReturn([]);
        $managedUser->method('getGender')->willReturn('male');
        $managedUser->method('getElectMandates')->willReturn([]);
        $managedUser->method('getCommittee')->willReturn(null);
        $managedUser->method('getAgora')->willReturn(null);

        $context = ['groups' => [ManagedUserContextBuilder::GROUP_VOX]];

        $this->innerNormalizer->method('normalize')->willReturn(['uuid' => 'test']);

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('elect_mandates', $result);
        self::assertSame([], $result['elect_mandates']);
    }

    public function testSortMandatesByPrioritySingleElementReturnsSame(): void
    {
        $managedUser = $this->createMock(ManagedUser::class);
        $managedUser->method('getRoles')->willReturn([]);
        $managedUser->method('getGender')->willReturn('male');
        $managedUser->method('getElectMandates')->willReturn([MandateTypeEnum::MAIRE]);
        $managedUser->method('getCommittee')->willReturn(null);
        $managedUser->method('getAgora')->willReturn(null);

        $context = ['groups' => [ManagedUserContextBuilder::GROUP_VOX]];

        $this->innerNormalizer->method('normalize')->willReturn(['uuid' => 'test']);
        $this->translator->method('trans')->willReturnCallback(function (string $key) {
            return $key;
        });

        $result = $this->normalizer->normalize($managedUser, null, $context);

        self::assertArrayHasKey('elect_mandates', $result);
        self::assertCount(1, $result['elect_mandates']);
        self::assertSame(MandateTypeEnum::MAIRE, $result['elect_mandates'][0]['code']);
    }
}
