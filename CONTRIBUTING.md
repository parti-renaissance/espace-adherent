# Guide de contribution

Merci de l'intérêt que vous portez à **Renaissance Plateforme**. Ce guide décrit comment contribuer efficacement au projet.

---

## Avant de commencer

1. **Forkez** le repository et clonez votre fork en local
2. **Installez** le projet en suivant [docs/1-Installer-le-projet-en-local.md](docs/1-Installer-le-projet-en-local.md)
3. **Consultez** les [issues ouvertes](https://github.com/parti-renaissance/espace-adherent/issues) pour trouver une tâche

> Pour tout changement significatif, ouvrez d'abord une issue pour en discuter avec l'équipe avant d'écrire du code. Cela évite les PRs orphelines.

---

## Workflow de développement

Nous utilisons un modèle **une branche par fonctionnalité / correctif**.

```bash
# 1. Synchroniser votre fork avec le repo principal
git remote add upstream git@github.com:parti-renaissance/espace-adherent.git
git fetch upstream
git checkout master && git merge upstream/master

# 2. Créer une branche depuis master
# Convention : <type>/<numéro-issue>-<description-courte>
git checkout -b fix/1234-correction-affichage-evenement
git checkout -b feat/5678-export-csv-adherents

# 3. Développer, committer (voir conventions ci-dessous)
# 4. Pousser et ouvrir une PR vers master
git push origin fix/1234-correction-affichage-evenement
```

### Types de branches

| Préfixe | Usage |
|---|---|
| `feat/` | Nouvelle fonctionnalité |
| `fix/` | Correctif de bug |
| `chore/` | Maintenance, dépendances, config |
| `docs/` | Documentation uniquement |
| `refactor/` | Refactoring sans changement de comportement |

---

## Conventions de commit

Format : `<type>(<scope>): <sujet>`

```
feat(event): ajout de l'export PDF pour les événements
fix(donation): correction du calcul de la TVA sur les reçus fiscaux
chore(deps): mise à jour de symfony/security-bundle 7.3 → 7.4
docs(contributing): mise à jour du guide de contribution
```

**Types :** `feat`, `fix`, `chore`, `docs`, `refactor`, `test`, `perf`

**Scope :** nom du module concerné (`event`, `pap`, `donation`, `oauth`...) — facultatif mais recommandé.

**Sujet :** impératif, minuscule, sans point final, en français.

---

## Standards de code

Ce projet suit les standards **PSR-12** et les bonnes pratiques Symfony.

```bash
# Vérifier le style avant de committer
php vendor/bin/php-cs-fixer fix --dry-run --diff

# Appliquer les corrections automatiquement
php vendor/bin/php-cs-fixer fix
```

L'analyse statique est assurée par PHPStan au niveau 6 :

```bash
php bin/phpstan analyse
```

---

## Tests

Toute PR doit passer les tests existants et, dans la mesure du possible, inclure des tests pour les nouveaux comportements.

```bash
# Lancer tous les tests
php bin/phpunit

# Lancer les tests d'un module spécifique
php bin/phpunit tests/Event/

# Lancer un test précis
php bin/phpunit tests/Event/EventManagerTest.php --filter testCreateEvent
```

Nous utilisons **PHPUnit** pour les tests unitaires et fonctionnels. Les tests Behat (scénarios BDD) sont dans `features/`.

---

## Ouvrir une Pull Request

Avant de soumettre :

- [ ] Les tests passent (`php bin/phpunit`)
- [ ] L'analyse statique ne remonte pas de nouvelles erreurs (`php bin/phpstan analyse`)
- [ ] Le code est formaté (`php vendor/bin/php-cs-fixer fix --dry-run`)
- [ ] Le titre de la PR suit le format de commit (`feat(scope): description`)
- [ ] La PR est liée à une issue si elle en résout une (`Closes #1234`)

Les PRs sont reviewées par l'équipe tech de Renaissance. Comptez **2 à 5 jours ouvrés** pour un premier retour.

Nous pouvons demander des modifications — ne le prenez pas personnellement, c'est notre façon de maintenir un haut niveau de qualité.

---

## Environnement recommandé

| Outil  | Recommandation                                |
|--------|-----------------------------------------------|
| IDE    | PHPStorm + plugin Symfony                     |
| Git    | Version ≥ 2.40                                |
| PHP    | 8.5 avec extensions `intl`, `gd`, `pdo_mysql` |
| Docker | Docker Desktop ou OrbStack                    |

---

## Questions ?

Ouvrez une [issue GitHub](https://github.com/parti-renaissance/espace-adherent/issues) ou commentez directement sur l'issue concernée.
