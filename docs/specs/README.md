# docs/specs/

Specs ingénierie pour les chantiers significatifs (>2 fichiers, >50 lignes, ou risque non trivial). Une spec sert à figer la cible avant d'écrire du code.

## Format

`docs/specs/<YYYY-MM-DD>-<slug>-design.md`. La skill `writing-plans` génère ce format.

```markdown
# <Titre> — design

**Auteur(s)** : prénom
**Date** : YYYY-MM-DD
**Statut** : draft | accepté | en cours | livré

## Contexte

Pourquoi ce chantier ? Quel est le problème métier / technique ?

## Cible

Ce qu'on veut atteindre, formulé concrètement.

## Approche

Décisions techniques principales, alternatives considérées.

## Plan d'exécution

Phasage par PR, avec ordre et dépendances.

## Risques

Ce qui peut casser, comment on mitige.
```

## Specs en cours

(aucune)
