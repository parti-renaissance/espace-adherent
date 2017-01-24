# 1.3 Utilisation du Makefile

[Précédent : 1.2 Utilisateurs de Docker](1-2-Utilisateurs-de-docker.md) -
[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)

Un fichier Makefile est disponible et vous permettra d'automatiser certaines tâches. Toutes les commandes qu'il propose
sont évidemment réalisables via les commandes standards et son utilisation n'est donc pas obligatoire.

Que vous soyez utilisateur de Docker ou que vous préfériez installer les services unitairement sur votre machine,
ce Makefile est compatible et peut vous faciliter le développement.

Veuillez noter qu'une detection de la présence de la commande `docker-compose` est éffectuée par ce Makefile afin de
déterminer quelles sont les commandes à éxecuter. Ceci peut ne pas convenir dans tous les cas.

## 1.3.1 Commandes unitaires

***Les commandes suivantes ont une seule tâche précise.***

Démarre les services (uniquement le serveur web embarqué pour ceux qui n'utilisent pas Docker)

```bash
make start
```

Arrête les services (n'a d'effet que pour les utilisateurs de Docker):

```bash
make stop
```

Installer les dépendances PHP avec Composer (composer install) :

```bash
make composer
```

Installer les dépendances JavaScript et CSS avec Yarn (yarn install) :

```bash
make yarn
```

Rétablir les droits sur le dossier `var`:

```bash
make perm
```

Nettoyer tous les dossiers temporaires, de cache, de sessions, de logs... (sans réamorçage des caches) :

```bash
make clean
```

Rétablir la base de données dans un état *propre* (supprime puis rétablit le schéma et les données de test):

```bash
make db
```

Compiler les JavaScripts et CSS (de façon unique et ponctuelle) :

```bash
make assets
```

Surveiller les modifications sur les JavaScripts et CSS afin qu'ils soient compilés automatiquement (watch) :

```bash
make watch
```

Executer les tests :

```bash
make test
```

## 1.3.2 Commandes composites

***Les commandes suivantes sont des enchaînements des tâches précédentes.***

La commande nue permet de : lancer les services, installer les dépendances (PHP, JavaScripts et CSS), compiler les
assets, mettre en place la base de données et les données de tests (passe par une suppression), corriger les permissions
du dossier `var`.

```bash
make
```

Installer les dépendances (PHP, JavaScripts et CSS), compiler les assets, corriger les permissions du dossier `var`.
```bash
make dependencies
```

[Précédent : 1.2 Utilisateurs de Docker](1-2-Utilisateurs-de-docker.md) -
[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)
