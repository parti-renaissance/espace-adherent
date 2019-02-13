# 2. Architecture du projet

[Précédent : 1. Installer le projet en local pour développer](1-Installer-le-projet-en-local.md) -
[Suivant : 3. Processus de développement](3-Processus-de-développement.md)

Une fois que vous avez récupéré le projet en local et que vous l'avez installé, vous devriez avoir
l'architecture de fichiers suivantes dans le dossier du projet :

```
- app
- bin
- docs
- front
- node_modules
- src
- tests
- var
- vendor
- web
```

Cette architecture est classique d'un projet Symfony à deux exceptions près : le dossier `node_modules` est le lieu
où sont stockées les dépendances JavaScript et Sass du projet, et le dossier `front` est là où est stocké le code de
la partie cliente du projet (Sass/JSX).


## Travailler sur la partie serveur (Symfony)

### Pré-requis

Afin d'appréhender correctement la partie serveur du projet, nous vous conseillons de connaitre un minimum Symfony.
Cela n'est pas forcément requis si vous corrigez un problème ou que vous effectuez une petite modification, mais cela
sera toujours utile de bien comprendre le fonctionnement du projet.

Si vous ne connaissez pas Symfony, vous pouvez apprendre grâce au
[tutoriel d'OpenClassroom sur le sujet](https://openclassrooms.com/courses/developpez-votre-site-web-avec-le-framework-symfony)
ou encore grâce au [tutoriel officiel (en anglais)](http://symfony.com/doc/current/index.html) qui est très bien fait.

Des notions d'orienté objet sont nécessaires pour bien comprendre Symfony, si possible en PHP mais pas nécessairement.

### Description de l'architecture de dossiers

Pour travailler sur la partie Symfony du projet, les dossiers importants sont `app`, `src` et `tests`.

Symfony suit le modèle MVC (Modèle - Vue - Contrôleur) : à chaque page du site internet est donc associée un contrôleur
et une ou plusieurs vues.

Pour trouver toutes les pages du site internet, allez dans le dossier `src/Controller`. Chaque fichier contient
une classe de contrôleur contenant plus actions (plusieurs pages).

Si vous souhaitez créer une nouvelle page, créez simplement une nouvelle action avec une route dédiée (l'URL à laquelle
il sera possible d'accéder à votre page).

Si vous souhaitez résoudre un bug, la première chose est généralement de trouver la page où se produit le bug. Vous pouvez
investiguer dans ce dossier.

Ce document n'étant pas un tutoriel sur Symfony, nous n'allons pas plus décrire Symfony qui de nombreuses ressources dédiées
sur Internet.

### Outils additionnels à Symfony

#### Sentry

https://sentry.io

Outil de logging des erreurs de la plateforme.

#### Rasmey UUID Doctrine

https://github.com/ramsey/uuid-doctrine

Petite librairie permettant de manipuler dans les entités Doctrine des UUID au lieu d'entiers.

#### DoctrineExtensionsBundle

https://symfony.com/doc/current/bundles/StofDoctrineExtensionsBundle/index.html

Extensions Doctrine très utiles pour certaines fonctionnalités avancées, comme :

- la gestion du stockages d'arbres de données
- la traduction de contenus en base de donnée
- la génération de slugs
- la gestion automatique des dates de création/mise à jour
- etc.

#### DoctrineMigrationsBundle

http://symfony.com/doc/current/bundles/DoctrineMigrationsBundle/index.html

Outil très utiles permettant de versionner une base de données afin de réaliser des migrations de la base de données
lors des déploiements en production.

#### DoctrineFixturesBundle et Faker

http://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html
https://github.com/fzaninotto/Faker

L'association de ces deux outils permet de générer des données aléatoires pour le développement et les tests.
Il est par exemple possible grâce à ces outils de précharger la base de données au moment des tests avec des utilisateurs
générés aléatoirement.

#### League Flysystem

https://flysystem.thephpleague.com
https://packagist.org/packages/superbalist/flysystem-google-storage

Flysystem fournit une abstraction du système de fichiers qui permettant d'effectuer des tests automatisés
aisément (simple de créer un faux système de fichiers) et de développer en local puis d'utiliser Google Storage en
production.


## Travailler sur la partie cliente (Sass/React)

### Pré-requis

Afin d'appréhender correctement la partie client du projet, nous vous conseillons de connaître un minimum React.
C'est un outil assez simple à comprendre tant que vous n'entrez pas dans les parties avancées.

La plateforme En Marche utilise React non pas comme un framework d'application mais comme un élément additionnel
pour ajouter du dynamisme, des interactions et des comportements user-friendly. L'utilisation de React y est donc assez
simple.

Si vous ne connaissez pas React, vous pouvez apprendre grâce à la documentation officielle (en anglais) qui est très bien faite:
[https://facebook.github.io/react/docs/hello-world.html](https://facebook.github.io/react/docs/hello-world.html).

Le projet utilise React avec ES6 (la dernière version de JavaScript, disposant de classes). Certaines notations sont
différentes mais les concepts d'orienté objets utilisés sont très limités.

La partie la plus importante est de comprendre que votre code est compilé grâce à Webpack. Vous devrez donc compiler le
code que vous écrivez pour l'essayer. Pour vous simplifier la tâche, vous pouvez lancer un outil qui compilera le code
automatiquement à chacune de vos modifications :
[lisez la documentation d'installation à ce sujet](https://github.com/EnMarche/en-marche.fr/blob/master/docs/1.%20Installer%20le%20projet%20en%20local.md#e-compilation-continuelle-du-css-et-du-javascript).

### Description de l'architecture de dossiers

Pour travailler sur la partie cliente du projet, le dossier important est `front`.

Vous y trouverez les styles en Sass (dossier `style`), les composants React et les tests automatisés.

Les trois fichiers à la racine de ce dossier sont les suivants :

- `app.js` constitue l'application en elle-même : chacune des méthodes de cette classe est une sorte de contrôleur
  (modèle MVC) ayant pour objectif d'appeler vos composants React. Chaque méthode de cette classe doit être très courte
  (pas plus de 3-4 lignes) ;
- `vendor.js` est un fichier rassemblant toutes les dépendances JavaScript du projet. Cette séparation par rapport à
  l'application permet de meilleures performances car le cache HTTP des dépendances devient indépendant du cache HTTP de
  l'application ;
- `kernel.js` permet de charger l'application et les vendors. Il est très léger pour rapidement charger en asynchrone
  l'application JavaScript ;

Lorsque vous lancez Webpack (au travers des commandes `npm run build-dev` et `npm run watch`), les fichiers `app.js`,
`kernel.js`, `vendor.js` et `style/app.scss` seront compilés en CSS et JavaScript et stockés dans le dossier `public/built`.

[Précédent : 1. Installer le projet en local pour développer](1-Installer-le-projet-en-local.md) -
[Suivant : 3. Processus de développement](3-Processus-de-developpement.md)
