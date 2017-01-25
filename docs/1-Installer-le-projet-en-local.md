# 1. Installer le projet en local pour développer

[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)

La plateforme en-marche.fr est basée sur les outils open-source suivants :

- [Symfony](http://symfony.com/), framework PHP développé par la société française [SensioLabs](https://sensiolabs.com/fr)
- [Sass](http://sass-lang.com/), language étendant les possibilités de CSS
- [Webpack](https://webpack.github.io/docs/), aggrégateur de modules JavaScript
- [MariaDB](https://mariadb.org/), moteur de base de donnée dérivé de MySQL

Si vous avez la moindre question ou le moindre problème pour mettre en place votre environnement de développement,
n'hésitez pas à nous poser la question [sur Slack](https://slack.en-marche.fr).

## 1.1.1 Installer les pré-requis du projet

Le projet En Marche utilise [Docker](https://www.docker.com/), un outil de container permettant de mettre en
place un environnement de développement rapidement. Le projet n'a donc que trois prérequis :

- Docker (1.12+)
- docker-compose (1.10+)
- GNU make

Pour installer Docker, référez-vous à la documentation officielle adaptée à votre système d'exploitation :
https://docs.docker.com/engine/installation/.

Une fois Docker installé, pour vérifier son bon fonctionnement, exécutez `docker -v`, vous devriez obtenir quelque
chose comme suit :

```
$ docker -v
Docker version 1.12.4, build 1564f02
```

> Vous devez utiliser la version 1.12 minimum de Docker.

Pour installer docker-compose, référez-vous là aussi à la documentation officielle :
https://docs.docker.com/compose/install/.

Une fois docker-compose installé (installez-le globalement pour pouvoir y accéder depuis n'importe où),
pour vérifier son bon fonctionnement, exécutez `docker-compose -v`, vous devriez obtenir quelque chose comme suit :

```
$ docker-compose -v
docker-compose version 1.10.0, build 4bd6f1a
```

> Vous devez utiliser la version 1.10 minimum de docker-compose.

## 1.1.2 Préparer le projet pour développer

Le projet fonctionne grâce à différents containers Docker coordonnés par docker-compose. De plus, un Makefile vous
permettra d'exécuter les actions classiques lors du développement (vider le cache, fabriquer les assets, etc.).
Il faut donc que vous puissiez exécuter `make -v`, ce qui devrait vous donner quelque chose comme :

```
$ make -v
GNU Make 4.1
Construit pour x86_64-pc-linux-gnu
Copyright (C) 1988-2014 Free Software Foundation, Inc.
Licence GPLv3+ : GNU GPL version 3 ou ultérieure <http://gnu.org/licenses/gpl.html>
Ceci est un logiciel libre : vous êtes autorisé à le modifier et à la redistribuer.
Il ne comporte AUCUNE GARANTIE, dans la mesure de ce que permet la loi.
```

> **Note**: si vous utilisez Windows, nous vous recommandons très fortement d'utiliser la console Linux intgrée à
> Windows 10 (https://msdn.microsoft.com/fr-fr/commandline/wsl/install_guide) ou d'utiliser un émulateur de ligne de
> commande pour pouvoir utiliser `make` qui vous facilitera grandement le travail.

Si Docker, docker-compose et make fonctionnent correctement, vous êtes prêt à préparer le projet pour développer.

### 1.1.2.1 Cloner le projet

Clonez votre fork du repository Git quelque part sur votre machine puis allez dans le dossier créé :

```
$ git clone git@github.com:<votre-fork>/en-marche.fr.git
$ cd en-marche.fr
```

Vous devriez alors pouvoir lancer `make` qui vous affichera l'aide du Makefile :

```
$ make
help:            Show this help
install:         [start deps db-create] Setup the project using Docker and docker-compose
start:           Start the Docker containers
stop:            Stop the Docker containers
deps:            [composer yarn assets-dev perm] Install the project PHP and JS dependencies
composer:        Install the project PHP dependencies
yarn:            Install the project JS dependencies
db-create:       Create the database and load fixtures in it
db-update:       Update the database structure according to the last changes
cache-clear:     Clear the application cache in development
clean:           Deeply clean the application (remove all the cache, the logs, the sessions and the built assets)
perm:            Fix the application cache and logs permissions
assets:          Watch the assets and build their development version on change
assets-dev:      Build the development assets
assets-prod:     Build the production assets
test:            [test-php test-js] Run the PHP and the Javascript tests
test-php:        Run the PHP tests
test-js:         Run the Javascript tests
```

### 1.1.2.2 Lancer l'initialisation du projet

Tout d'abord, créez une copie du fichier `docker-compose.override.yml.dist` appelée `docker-compose.override.yml`
afin de choisir le port à utiliser pour le projet.

Lancez l'initialisation du projet avec `make install` :

```
$ make install          # Sous macOS ou si vous avez configuré votre utilisateur Linux pour Docker
$ sudo make install     # Sous Linux si vous avez simplement installé Docker
Pulling db (mariadb:latest)...
latest: Pulling from library/mariadb
...
```

Cela risque de prendre un peu de temps.

Une fois terminé, votre environnement de développement devrait être prêt et le projet devrait être accessible sur
[http://127.0.0.1:8000](http://127.0.0.1:8000) (ou si vous êtes sous macOS, sur le port 8000 de la machine virtuelle de
votre instance Docker).

## 1.1.3 Accéder à l'espace d'administration

Une fois le projet installé, vous pouvez accéder à l'espace d'administration en allant sur
[http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin).

Vous pouvez alors y entrer les identifiants suivants :
- admin@en-marche-dev.fr / admin pour l'accès en mode administrateur
- writer@en-marche-dev.fr / writer pour l'accès en mode rédacteur

## 1.1.4 Services disponibles

Docker et docker-compose vous ont permis d'initialiser très rapidement le projet. Pour cela, docker-compose a
créé 4 containers :

- `app`, l'application en elle-même
- `db`, la base de donnée utilisée par l'application
- `tools`, un container d'outils pour travailler sur le projet (Yarn notament)
- `pma`, PHPMyAdmin, pour travailler avec la base de donnée

Par défaut, si vous avez copié le `docker-compose.override.yml.dist` en `docker-compose.override.yml`, vous devriez
pouvoir accéder aux containers sur les ports suivants de votre machine locale :

- `app`, HTTP sur le port 8000
- `db`, MySQL sur le port 3306
- `pma`, HTTP sur le port 8080

[Suivant : 2. Architecture du projet](2-Architecture-du-projet.md)
