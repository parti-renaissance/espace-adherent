# Code review protocol (mode: mixed | human-team)

Toute PR sur ce projet passe par review humaine avant merge. Pour les PR ouvertes par Claude, le dev qui review reste responsable du code mergé.

## Règles dures

- **1 reviewer minimum**, idéalement le dev qui connaît le module touché.
- **Pas d'auto-merge** sans review humaine (sauf cas dependabot trivial documenté).
- Review obligatoire sur : changements d'archi, ADR modifié, fichiers `LOCKED`, tests retirés.
- CI verte avant review (économise du temps reviewer).

## Skills à utiliser

- **Demander une review** : skill `requesting-code-review` (Superpowers). Liste claire de ce qui change, contextualise.
- **Recevoir une review** : skill `receiving-code-review`. Pas de défensive, fix ou explique.
- **Adresser les comments** : skill `openai/skills --skill gh-address-comments`. Itérer jusqu'à approuvée.

## Format PR attendu

```markdown
## Pourquoi
<contexte / motivation, 2-3 lignes>

## Quoi
<résumé technique, 3-5 bullets>

## Test
- [ ] Tests ajoutés/modifiés
- [ ] CI verte
- [ ] Vérifié en local

## Risques
<si non évident — sinon "aucun connu">
```

## Cas spéciaux

- PR ouvertes par Claude (mode harness-solo) sont reviewées par le mainteneur du projet, comme une PR humaine.
- Si désaccord technique non résolu : ouvrir un ADR pour trancher.
