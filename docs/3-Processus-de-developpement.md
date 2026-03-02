# 3. Processus de développement

[Précédent : 2. Architecture du projet](2-Architecture-du-projet.md)

---

## Principes

- **Une branche par sujet** — travaillez toujours sur une branche dédiée, jamais directement sur `master`
- **PR tôt, PR souvent** — ouvrez une PR dès que vous avez du code à montrer, même incomplet. Utilisez le statut "Draft" pour signaler qu'elle n'est pas prête
- **Tests systématiques** — tout nouveau comportement doit être couvert par un test ; tout bug corrigé doit avoir son test de non-régression
- **Commentez sur l'issue** — signalez sur l'issue que vous commencez à travailler dessus pour éviter les doublons

---

## Développer une fonctionnalité

1. Créez votre branche : `git checkout -b feat/1234-ma-fonctionnalite`
2. Développez et testez en local
3. Ajoutez des tests automatisés
4. Ouvrez une PR vers `master` avec une description claire
5. Attendez la review de l'équipe (2 à 5 jours ouvrés)

---

## Corriger un bug

1. Écrivez d'abord un test qui expose le problème (il doit échouer)
2. Corrigez le bug jusqu'à ce que le test passe
3. Vérifiez que l'ensemble de la suite de tests passe toujours
4. Ouvrez une PR en référençant l'issue (`Closes #1234`)

---

## Lancer les tests en local

```bash
make tu   # tests unitaires
make tf   # tests fonctionnels (Behat + PHPUnit)
make test # tous les tests (unitaires + fonctionnels + JS)
```

---

## Synchroniser votre fork avec le repo principal

```bash
git remote add upstream git@github.com:parti-renaissance/espace-adherent.git
git fetch upstream
git checkout master
git merge upstream/master
git push
```

Mettez à jour les dépendances si elles ont changé :

```bash
make deps
```

---

## Avant de merger

- [ ] `make test` — tous les tests passent
- [ ] `make phpstan` — pas de nouvelles erreurs
- [ ] `make phpcsfix` — style PHP corrigé
- [ ] La branche est rebasée sur `master` (pas de conflits)
- [ ] Les commits sont propres (squash si besoin)

[Précédent : 2. Architecture du projet](2-Architecture-du-projet.md)
