# 1.2 Utilisateurs de Docker

[Précédent : 1.1 Installer le projet en local](1-1-Installer-le-projet-en-local.md) -
[Suivant : 1.3 Utilisation du Makefile](1-3-Utilisation-du-makefile.md)

Si vous utilisez Docker, vous pouvez installer et lancer le projet simplement avec :

```bash
$ cd /chemin/vers/le/projet
$ docker-compose up -d
```

Il sera ensuite nécessaire d'installer les dépendances ainsi que de configurer votre base de données.

La présence d'un Makefile peut vous aider dans l'éxecution des tâches courantes, vous pouvez sauter directement au
chapitre dédié : [1.3 Utilisation du Makefile](1-3-Utilisation-du-makefile.md)

## 1.2.1 Services et outils requis pour développer

Les services/outils suivants sont nécessaires pour développer :

- Docker et Docker-compose

### a. MariaDB

Un serveur de base de donnée MariaDB est requis pour travailler sur le projet.

Lancez simplement `docker-compose up -d pma` à la racine du projet.

Cela créera l'ensemble des services requis pour la base de données, comprenant un serveur web phpMyAdmin accessible sur
le port 8080 (que vous pouvez personnaliser dans le fichier `docker-compose.override.yml`) ainsi qu'un serveur
MariaDB accessible sur le port 3306 de votre machine (si vous êtes sous macOS ou Windows, vous passerez probablement
par l'adresse IP de la Docker-machine). La base de données est configurée automatiquement (utilisateur et mot de passe)
avec les variables d'environnement (que vous trouverez dans le fichier ``docker-compose.override.yml``).

### b. PHP

PHP 7.0 minimum est requis pour travailler sur le projet. PHP (en ligne de commande) est inclus dans le container `app`
et peut s'obtenir comme suit :

```bash
# Commencez par lancer le container
$ docker-compose up app

$ docker-compose exec app php -v

PHP 7.0.8-0ubuntu0.16.04.3 (cli)
Copyright (c) 1997-2016 The PHP Group
Zend Engine v3.0.0, Copyright (c) 1998-2016 Zend Technologies
    with Zend OPcache v7.0.8-0ubuntu0.16.04.3, Copyright (c) 1999-2016, by Zend Technologies
```

### c. Composer

Composer est le gestionnaire de dépendances de PHP. Il va vous permettre d'installer les dépendances du projet. Composer
est déjà installé et disponible dans le container `app` et peut s'obtenir comme suit :


```bash
# Commencez par lancer le container
$ docker-compose up app

$ docker-compose exec app composer --version

Composer version 1.3.1 2017-01-07 18:08:51
```

### d. Node.js / npm / Yarn

Node.js est requis pour compiler le Sass et le JSX en des fichiers utilisables par la plupart des navigateurs.

Node.js et ses outils additionnels sont disponible dans le container `tools` et peuvent s'obtenir comme suit :


```bash
$ docker-compose run tools node --version
v6.9.4

$ docker-compose run tools npm --version
3.10.10

$ docker-compose run tools yarn --version
0.19.1
```

Notez que le container `tools` n'exécute pas de daemon immédiatement lors de son lancement, vous utilisez donc la commande
run et non pas exec.

## 1.2.2 Lancer le projet en local

Une fois que vous avez bien vérifié que vos containers sont opérationnels, vous pouvez commencer à configurer le projet.

Commencez par lancer la totalité des containers avec la commande :

```bash
$ docker-compose up -d
```

### a. Installer les dépendances PHP du projet

Allez dans le dossier du projet et lancez Composer comme suit :

```bash
$ cd /chemin/vers/le/projet
$ docker-compose exec app composer install
```

Composer vous demandera des informations sur la base de donnée, le mailer et autre. A moins que vous ayez modifié les
valeurs de configuration lors de l'étape d'installation, vous pouvez appuyer sur <kbd>Entrée</kbd> pour choisir la
valeur proposée par Composer.

Un mailer n'est pas requis pour faire fonctionner la plateforme, cependant si vous utilisez le fichier
`docker-compose.yml` du projet, vous aurez un mailer de test disponible à l'URL
[http://localhost:9080](http://localhost:9080).

### b. Installer les dépendances JavaScript et CSS du projet

Allez dans le dossier du projet et lancez Yarn comme suit :

```bash
$ cd /chemin/vers/le/projet
$ docker-compose run tools yarn install
```

### c. Compiler le CSS et le JavaScript de développement

Allez dans le dossier du projet et lancez le script de build comme suit :

```bash
$ cd /chemin/vers/le/projet
$ docker-compose run tools npm run build-dev
```

Cette commande va créer les versions de développement des fichiers JavaScript et CSS nécessaires à l'affichage
de la plateforme dans le dossier `web/built`.

### d. Créer les entités de base

Allez dans le dossier du projet et lancez le script de build comme suit :

```bash
$ cd /chemin/vers/le/projet
$ docker-compose exec app bin/console doctrine:schema:create
$ docker-compose exec app bin/console doctrine:fixtures:load
$ docker-compose exec app bin/console app:content:prepare
```

Cette commande va créer des éléments de contenu de base pour pouvoir accéder à une page d'accueil fonctionnelle.

### e. Afficher la page de l'application

Une fois les dépendances du projet installées et prêtes (vous devriez désormais voir un dossier `vendor` dans le
dossier du projet), vous n'avez plus qu'une étape à réaliser : définir les droits d'écriture sur le dossier `var`.

En effet, lorsque vous exécutez une commande dans vos containers Docker, celle-ci s'exécute en `root`. Cela a pour
effet de gêner la création des fichiers de cache et de sessions. Pour remédier à cela, pensez à rétablir les droits sur
le dossier `var` à chaque fois que vous faites une opération d'écriture dans ce dossier (c'est notamment le cas du
`cache:clear` ou des scripts embarqués dans le `composer.json`).

```bash
$ docker-compose exec app chmod -R 777 var
```

Vous pouvez accéder à l'URL [http://127.0.0.1](http://127.0.0.1), vous devriez maintenant voir le projet.

### f. Compilation continuelle du CSS et du JavaScript

D'autre part, si vous faites des modifications sur le front-end, vous voudrez 
sûrement que vos changements soient pris en compte en temps réel. Cela vous 
permettra de ne pas avoir à relancer la compilation du CSS et du JavaScript 
après chaque modification de code.

```bash
$ cd /chemin/vers/le/projet
$ docker-compose run tools npm run watch
```

[Précédent : 1.1 Installer le projet en local](1-1-Installer-le-projet-en-local.md) -
[Suivant : 1.3 Utilisation du Makefile](1-3-Utilisation-du-makefile.md)
