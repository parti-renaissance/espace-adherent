# docs/adrs/

Architecture Decision Records pour les décisions techniques durables (LOCKED) ou évolutives (DESIGN).

Une ADR est utile quand une décision :
- A des conséquences architecturales lourdes
- Mérite un débat (alternatives sérieuses considérées)
- Est non triviale à inférer depuis le code

Pour les décisions de process / tooling / organisation, l'ADR est aussi le bon support (cf. ADR-0001 ci-dessous).

## Format

`docs/adrs/<NNNN>-<slug>.md` (numérotation séquentielle).

```markdown
# ADR-NNNN — Titre court à l'impératif

- **Statut** : proposé | accepté | déprécié | remplacé par ADR-XXXX
- **Date** : YYYY-MM-DD
- **Auteur(s)** : prénom

## Contexte
## Décision
## Conséquences
### Positives
### Négatives / coûts
### À faire suite à la décision
## Alternatives considérées
```

## Index

| # | Titre | Statut |
|---|---|---|
| 0001 | (à venir, voir PR de bootstrap socle) | proposé |
