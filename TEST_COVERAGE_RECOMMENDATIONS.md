# Recommandations pour l'amélioration de la couverture de tests

## État actuel

| Indicateur | Valeur |
|---|---|
| Fichiers PHP source (`src/`) | ~2 999 |
| Fichiers de test (`tests/`) | ~208 |
| Ratio estimé | ~7 % |
| Domaines `src/` sans répertoire de test | 70+ |

Les tests existants sont quasi-exclusivement des **tests fonctionnels / web** (WebTestCase, ControllerTestCase). Les tests unitaires purs sont rares et cantonnés à `tests/Unit/Entity/` et `tests/Unit/Mailchimp/`.

---

## Axes d'amélioration prioritaires

### 1. `Normalizer` — Priorité haute

**Aucun test existant** pour les 71 normaliseurs/dénormaliseurs de `src/Normalizer/`.

Ces classes contiennent de la logique de sérialisation critique (API publique, imports/exports). Les bugs ici sont silencieux et impactent directement les clients de l'API.

Fichiers à couvrir en premier :

| Fichier | Pourquoi |
|---|---|
| `PhoneNumberNormalizer.php` | Logique de branchement selon les groupes de contexte, plusieurs chemins de dénormalisation |
| `DateTimeNormalizer.php` | Décorateur qui avale silencieusement les `NotNormalizableValueException` |
| `AdherentNormalizer.php` | Normaliseur central de l'entité principale |
| `AdherentMessageNormalizer.php` | Logique de normalisation complexe (132 lignes) |

**Pattern recommandé** : `PHPUnit\Framework\TestCase` pur — aucune dépendance Symfony, pas de base de données.

```php
// Exemple — tests/Unit/Normalizer/PhoneNumberNormalizerTest.php
final class PhoneNumberNormalizerTest extends TestCase
{
    private PhoneNumberNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new PhoneNumberNormalizer();
    }

    public function testNormalizeReturnsArrayForProfileReadGroup(): void { ... }
    public function testNormalizeReturnsInternationalStringByDefault(): void { ... }
    public function testDenormalizeHandlesInvalidString(): void { ... }
}
```

---

### 2. `Adhesion` — Priorité haute

**Aucun test existant** pour le flux d'adhésion (inscription, activation du compte).

`ActivationCodeManager` gère la validation du code de confirmation avec rate-limiting et plusieurs chemins d'erreur — c'est exactement le type de logique qui doit être testé unitairement.

Fichiers à couvrir :

| Fichier | Pourquoi |
|---|---|
| `ActivationCodeManager.php` | 6 exceptions différentes, dispatche un événement en cas de succès |
| `Handler/` | Handlers de commandes CQRS — logique métier pure |
| `AdherentRequestNotifier.php` | Notifications transactionnelles |

**Pattern recommandé** : `PHPUnit\Framework\TestCase` avec mocks pour `EntityManagerInterface`, `RateLimiterFactory`, `EventDispatcherInterface` et `AdherentActivationCodeRepository`.

```php
public function testValidateThrowsWhenRateLimitExceeded(): void
{
    $limiter = $this->createMock(RateLimit::class);
    $limiter->method('isAccepted')->willReturn(false);
    // ...
    $this->expectException(ActivationCodeRetryLimitReachedException::class);
    $this->manager->validate('123456', $adherent);
}
```

---

### 3. `Procuration` — Priorité haute

**Un seul test existant** (`RemindProcurationMatchedSlotCommandTest`) pour l'ensemble du domaine de procuration de vote.

`ProcurationHandler` implémente une machine à états pour le matching mandant/mandataire — logique critique, zéro couverture.

Fichiers à couvrir :

| Fichier | Pourquoi |
|---|---|
| `ProcurationHandler.php` | Machine à états `PENDING ↔ COMPLETED`, logique de matching/dématching |
| `ProcurationFactory.php` | Factory créant `Request` et `Proxy` avec leurs slots |
| `MatchingHistoryHandler.php` | Traçabilité des actions de matching |
| `ProcurationActionHandler.php` | Création des actions sur les slots |

**Pattern recommandé** : `PHPUnit\Framework\TestCase` avec de vraies instances d'entités Procuration (elles sont instanciables sans DB) et `EntityManagerInterface` mocké.

```php
public function testUpdateRequestStatusChangesToCompletedWhenNoFreeSlot(): void
{
    $request = $this->buildFullyMatchedRequest(); // tous les slots pris
    $this->entityManager->expects($this->once())->method('flush');

    $this->handler->updateRequestStatus($request);

    $this->assertTrue($request->isCompleted());
}
```

---

### 4. `Membership/AdherentFactory` — Priorité haute

**Aucun test existant** pour la factory qui crée les adhérents depuis les différents flux d'inscription.

`createFromRenaissanceMembershipRequest()` contient de la logique métier subtile (tag par défaut, activation conditionnelle selon `originalEmail`).

**Pattern recommandé** : `PHPUnit\Framework\TestCase` avec `PasswordHasherFactoryInterface` et `AdherentPublicIdGenerator` mockés.

```php
public function testAdherentIsEnabledWhenOriginalEmailMatchesEmail(): void
{
    $request = new MembershipRequest();
    $request->email = 'user@example.com';
    $request->originalEmail = 'user@example.com'; // même email → activation directe

    $adherent = $this->factory->createFromRenaissanceMembershipRequest($request);

    $this->assertTrue($adherent->isEnabled());
}

public function testAdherentHasSympathisantTagByDefault(): void
{
    $adherent = $this->factory->createFromRenaissanceMembershipRequest($request);

    $this->assertContains(TagEnum::SYMPATHISANT_ADHESION_INCOMPLETE, $adherent->tags);
}
```

---

### 5. `NationalEvent` — Priorité moyenne

**Aucun test existant** pour un domaine qui gère inscriptions et paiements à des événements nationaux.

`EventInscriptionManager` contient de la logique complexe autour du calcul de montants, des remboursements et des statuts de paiement.

Fichiers à couvrir :

| Fichier | Pourquoi |
|---|---|
| `EventInscriptionManager.php` | Logique de paiement, calcul de montants, statuts |
| `Payment/RequestParamsBuilder.php` | Constructeur de paramètres de paiement |
| `Handler/` | Handlers CQRS |

---

### 6. `Validator` — Priorité moyenne

Sur les ~99 fichiers dans `src/Validator/`, seulement **5 sous-répertoires de tests** existent (`Email`, `Jecoute`, `Procuration`, `Scope`, `AdherentFormation`). Des dizaines de contraintes métier sont non testées.

Validators prioritaires à couvrir :

- `AdherentInterestsValidator`
- `BannedAdherentValidator`
- `CommitteeMemberValidator`

**Pattern recommandé** : Étendre `Symfony\Component\Validator\Test\ConstraintValidatorTestCase`.

---

### 7. `VotingPlatform` — Priorité moyenne

**1 seul test** (`tests/VotingPlatform/Election/`) pour 56 fichiers source. Le vote interne est critique et sensible.

---

## Recommandations transverses

### Préférer les tests unitaires aux tests fonctionnels là où c'est possible

Les tests fonctionnels actuels (WebTestCase + DB + fixtures) sont lents et fragiles. Pour les services et factories, les tests unitaires avec mocks sont :
- **20–100× plus rapides**
- **Plus ciblés** (un test = un comportement)
- **Maintenables** indépendamment des fixtures

### Convention de nommage à adopter

```
tests/
  Unit/                   ← PHPUnit\Framework\TestCase pur
    Normalizer/
    Membership/
    Adhesion/
    Procuration/
  Functional/             ← AbstractKernelTestCase (DB nécessaire)
    Repository/
    Pap/
  Controller/             ← WebTestCase (HTTP complet)
    ...
```

### Ordre de priorité suggéré

| # | Fichier de test à créer | Effort | Impact |
|---|---|---|---|
| 1 | `tests/Unit/Normalizer/PhoneNumberNormalizerTest.php` | Faible | Élevé |
| 2 | `tests/Unit/Membership/AdherentFactoryTest.php` | Faible | Élevé |
| 3 | `tests/Unit/Adhesion/ActivationCodeManagerTest.php` | Moyen | Élevé |
| 4 | `tests/Unit/Procuration/ProcurationHandlerTest.php` | Moyen | Élevé |
| 5 | `tests/Unit/NationalEvent/EventInscriptionManagerTest.php` | Moyen | Moyen |
| 6 | `tests/Unit/Validator/*` (validators manquants) | Faible × N | Moyen |
| 7 | `tests/VotingPlatform/` (compléter) | Élevé | Élevé |
