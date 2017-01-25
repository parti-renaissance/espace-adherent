# 3. Processus de développement

[Précédent : 2. Architecture du projet](2-Architecture-du-projet.md)

## Organisation et conseils

Le processus de développement de la plateforme En Marche est basé sur certains fondamentaux :

- Lorsque vous travaillez sur un sujet, annoncez-le sur l'issue associée au sujet afin de tenir tout le monde
  informé de votre travail ;
- Créez une pull request le plus rapidement possible (même si elle ne contient que très peu de code) et marquez là
  "En cours" : cela permettra à tous de voir l'avancement de votre travail et cela vous permettra à vous de lancer
  les tests automatiques et les analyses du style de code ;
- A chaque ajout, modification et suppression de code, il est important de garder une suite de tests à jour et
  fonctionnelle. En testant ainsi toutes les fonctionnalités que nous ajoutons à la plateforme, nous serons capables
  dans le futur de corriger des bugs et d'ajouter des fonctionnalités en étant certains que ces changements n'entraîneront
  pas de problèmes.

Typiquement, lorsque vous développez sur la plateforme En Marche, nous vous conseillons suivre les étapes suivantes :

### Si vous développez une nouvelle fonctionnalité

1. Développez votre fonctionnalité
2. En parallèle, manipulez manuellement le site internet en local pour tester votre code 
  (il vous faudra bien sûr [avoir le projet en local](https://github.com/EnMarche/en-marche.fr/blob/archi-documentation/docs/1.%20Installer%20le%20projet%20en%20local.md))
3. Une fois que vous pensez avoir terminé votre fonctionnalité, ajoutez des tests automatisés afin de vérifier le bon
   fonctionnement de la fonctionnalité. Cette étape sera utile tout au long du projet pour vérifier que votre fonctionnalité
   est encore stable après des changements dans le code. Il est important que vous testiez tous les scénarios possibles
   de votre fonctionnalités afin que nous soyons alertés si un problème se produit avec.

### Si vous corrigez un problème

Dans le cadre de la résolution de problème, il est conseillé de démarrer par créer un test automatisé exposant le problème
(un test automatisé ne passant pas les vérifications car il montre qu'il y a un problème). De cette manière, une fois le
problème exposé avec le test, vous pouvez aisément vérifier sa résolution, et en l'ajoutant à notre suite de tests dans le
projet, nous serons assurés que ce problème ne réapparaitra pas.


## Lancer les tests automatisés en local

Afin de développer correctement, vous devez bien évidemment 
[avoir installé le projet en local](https://github.com/EnMarche/en-marche.fr/blob/archi-documentation/docs/1.%20Installer%20le%20projet%20en%20local.md)
mais aussi être capable de lancer les tests automatisés afin de vous assurer que votre code n'a pas entrainé de problème
dans le reste de l'application.

Il y a deux groupes de tests automatisés : les tests serveur (Symfony) et les tests client (React/JSX).

### Lancer les tests serveur

Lorsque vous êtes dans le dossier du projet, lancez la commande suivante :

```
make test-php
```

### Lancer les tests client

Lorsque vous êtes dans le dossier du projet, lancez la commande suivante :

```
make test-js
```


## Récupérer les modifications du projet principal

Lorsque vous travaillez, votre code est stocké dans votre "fork", une copie du repository principal dans votre espace
personnel. Lorsque vous travaillez sur le code, vous aurez sans doute besoin de mettre à jour votre repository personnel
à partir du repository principal.

Pour cela, dans le répertoire du projet, quand vous souhaitez synchroniser votre repository personnel avec le principal,
lancez les commandes suivantes :

```
$ cd /dossier/du/projet
$ git checkout master
$ git pull git@github.com:EnMarche/en-marche.fr.git
$ git push
```

Une fois synchronisé, vous devez aussi synchroniser les dépendances PHP et JavaScript (si jamais elles ont changé
par rapport au repository principal) :

```
$ cd /dossier/du/projet
$ make deps
```

[Précédent : 2. Architecture du projet](2-Architecture-du-projet.md)
