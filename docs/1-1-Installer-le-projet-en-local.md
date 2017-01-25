# 1.1 Installer le projet en local pour développer

[Suivant : 1.2 Utilisateurs de Docker](1-2-Utilisateurs-de-docker.md)

La plateforme en-marche.fr est basée sur les outils open-source suivants :

- [Symfony](http://symfony.com/), framework PHP développé par la société française [SensioLabs](https://sensiolabs.com/fr)
- [React](https://facebook.github.io/react/), framework JavaScript développé par Facebook
- [Sass](http://sass-lang.com/), language étendant les possibilités de CSS
- [Webpack](https://webpack.github.io/docs/), aggrégateur de modules JavaScript
- [MariaDB](https://mariadb.org/), moteur de base de donnée dérivé de MySQL

Pour développer en local sur le projet, il vous faudra donc installer et configurer certaines dépendances.

Si vous avez la moindre question ou le moindre problème pour mettre en place votre environnement de développement,
n'hésitez pas à nous poser la question [sur Slack](https://slack.en-marche.fr).

Si vous souhaitez utiliser Docker, vous pouvez sauter directement au chapitre dédié :
[1.2 Utilisateurs de Docker](1-2-Utilisateurs-de-docker.md)

## 1.1.1 Services et outils requis pour développer

Les services/outils suivants sont nécessaires pour développer :

- PHP 7.0+
- MariaDB 5.5+ ou MySQL 5.5+
- Composer
- Node.js, npm, Yarn

La présence d'un Makefile peut vous aider dans l'éxecution des tâches courantes, vous pouvez sauter directement au
chapitre dédié : [1.3 Utilisation du Makefile](1-3-Utilisation-du-makefile.md)

### a. MariaDB

Un serveur de base de donnée MariaDB est requis pour travailler sur le projet.

- Installez MariaDB ou MySQL (cela n'a pas d'importance, les deux fonctionneront) pour votre plateforme.
- Créez un utilisateur `enmarche` / `enmarche`
- Créez une base de donnée `enmarche` sur laquelle l'utilisateur `enmarche` dispose des droits complets

#### Initialisation de la base de données

Une fois la base de données créer il faut l'initialiser et insérer une ligne dans la table administators

##### Lancer mariadb avec docker

```
make boot 
```

##### Créer les tables

```
php bin/console doctrine:schema:update --force
php bin/console app:content:prepare
```

##### Créer un mot de passe pour l'administrateur

php bin/console security:encode-password monpassword AppBundle\\Entity\\Adherent

Le résultat contiendra le mot de passe encodé qu'il faudra insérer dans la table administrators

```
  Encoder used       Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder  
  Encoded password   $2y$13$yzAjwWdVy0o/CPD5y.D4/.xjriZKs9Zj2SIoXzGq.3GPU6MGbvypu   
```

##### Insérer une ligne dans la base

Il faut ensuite insérer une ligne dans la base de données, par la commande suivante:

```
INSERT INTO administrators VALUES(1,'monemail@mondomaine.com','$2y$13$yzAjwWdVy0o/CPD5y.D4/.xjriZKs9Zj2SIoXzGq.3GPU6MGbvypu','','ROLE_ADMIN');
```

##### Script complet d'initialisation

```
php bin/console doctrine:schema:update --force
php bin/console app:content:prepare
php bin/console security:encode-password monpassword AppBundle\\Entity\\Adherent | grep password | cut -d ' ' -f 7 > password.txt
PASSWORD=`cat password.txt`
mysql --host=127.0.0.1 -u enmarche -penmarche enmarche -e "INSERT INTO administrators VALUES(1,'monemail@mondomaine.com','$PASSWORD','','ROLE_ADMIN');"
```

### b. PHP

PHP 7.0 minimum est requis pour travailler sur le projet. Installez-le pour votre plateforme de telle sorte qu'il
soit accessible globalement en ligne de commande. Si vous tapez `php --version`, vous devez obtenir quelque chose
comme :

```
PHP 7.0.8-0ubuntu0.16.04.3 (cli)
Copyright (c) 1997-2016 The PHP Group
Zend Engine v3.0.0, Copyright (c) 1998-2016 Zend Technologies
    with Zend OPcache v7.0.8-0ubuntu0.16.04.3, Copyright (c) 1999-2016, by Zend Technologies
```

### c. Composer

Composer est le gestionnaire de dépendances de PHP. Il va vous permettre d'installer les dépendances du projet.

Pour l'installer, nous vous conseillons de suivre la documentation : https://getcomposer.org/doc/00-intro.md.
Choisissez, si vous le pouvez, d'installer Composer globalement.

Pour vérifier que Composer fonctionne correctement, lancez `composer --version`. Vous devriez obtenir quelque chose
comme :

```
Composer version 1.2.1 2016-09-12 11:27:19
```

### d. Node.js / npm / Yarn

Node.js est requis pour compiler le Sass et le JSX en des fichiers utilisables par la plupart des navigateurs.

Installez Node.js pour votre plateforme sur https://nodejs.org/en/download/ ou en utilisant votre gestionnaire de
paquets préféré.

Pour vérifier que vous avez bien Node.js et npm installés, lancez les commandes suivantes :

```bash
$ node --version
v6.9.1

$ npm --version
3.10.8
```

Une fois npm disponible, utilisez-le pour installer Yarn (le gestionnaire de dépendance utilisé par le projet,
vous aurez peut-être besoin d'exécuter cette commande en root) :

```bash
$ npm install -g yarn

# Pour vérifier l'installation
$ yarn --version
0.16.1
```

## 1.1.2 Lancer le projet en local

Une fois que vous avez PHP, MySQL ou MariaDB, Composer et Node.js et Yarn installés, vous pouvez commencer à configurer
le projet.

### a. Installer les dépendances PHP du projet

Allez dans le dossier du projet et lancez Composer comme suit :

```bash
$ cd /chemin/vers/le/projet
$ composer install
```

Composer vous demandera des informations sur la base de donnée, le mailer et autre. Vous pouvez appuyer sur
<kbd>Entrée</kbd> pour choisir la valeur proposée par Composer ou personnaliser en fonctions de vos paramètres
(notamment ceux concernant la base de données).

Une fois les dépendances PHP installées, vous pouvez vérifier que votre système est correctement configuré pour Symfony.
Pour cela, allez dans le dossier du projet et lancez `php bin/symfony_requirements`. Cela vous donnera quelque chose comme :
    
    ```
    Symfony Requirements Checker
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    
    > PHP is using the following php.ini file:
      /etc/php/7.0/cli/php.ini
    
    > Checking Symfony requirements:
      ................................W........
    
                                                  
     [OK]                                         
     Your system is ready to run Symfony projects 
                                                  
    
    Optional recommendations to improve your setup
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    
     * intl ICU version installed on your system is outdated (55.1) and
       does not match the ICU data bundled with Symfony (57.1)
       > To get the latest internationalization data upgrade the ICU
       > system package and the intl PHP extension.
    
    
    Note  The command console could use a different php.ini file
    ~~~~  than the one used with your web server. To be on the
          safe side, please check the requirements from your web
          server using the web/config.php script.
    ```
    
Notez bien que les recommandations optionnelles ne vous empêcheront pas de travailler sur le projet.

Si vous avez des erreurs, suivez simplement les recommandations de l'outil pour savoir quelles extensions
installer. Si finalement vous ne parvenez toujours pas à installer les extensions correctement, n'hésitez pas à
venir [sur Slack](https://slack.en-marche.fr) pour nous poser vos questions.


### b. Installer les dépendances JavaScript du projet

Allez dans le dossier du projet et lancez Yarn comme suit :

```bash
$ cd /chemin/vers/le/projet
$ yarn install
```

### c. Compiler le CSS et le JavaScript de développement

Allez dans le dossier du projet et lancez le script de build comme suit :

```bash
$ cd /chemin/vers/le/projet
$ npm run build-dev
```

Cette commande va créer les versions de développement des fichiers JavaScript et CSS nécessaires à l'affichage
de la plateforme dans le dossier `web/built`.

### d. Créer les entités de base

Allez dans le dossier du projet et lancez le script de build comme suit :

```bash
$ cd /chemin/vers/le/projet
$ php bin/console doctrine:schema:create
$ php bin/console doctrine:fixtures:load
$ php bin/console app:content:prepare
```

Cette commande va créer des éléments de contenu de base pour pouvoir accéder à une page d'accueil fonctionnelle.

### e. Lancer le serveur de développement

Une fois les dépendances du projet installées et prêtes (vous devriez désormais voir un dossier `vendor` dans le
dossier du projet), vous pouvez lancer le serveur de développement :

```bash
$ cd /chemin/vers/le/projet
$ php bin/console server:run


 [OK] Server running on http://127.0.0.1:8000


 // Quit the server with CONTROL-C.                                                                                     

PHP 7.0.8-0ubuntu0.16.04.3 Development Server started at Sun Dec 18 23:43:41 2016
Listening on http://127.0.0.1:8000
Document root is /home/tgalopin/projects/en-marche/en-marche.fr/web
Press Ctrl-C to quit.
```

Si vous accédez à l'URL [http://127.0.0.1:8000](http://127.0.0.1:8000), vous devriez maintenant voir le projet.

### f. Compilation continuelle du CSS et du JavaScript

D'autre part, si vous faites des modifications sur le front-end, vous voudrez 
sûrement que vos changements soient pris en compte en temps réel. Cela vous 
permettra de ne pas avoir à relancer la compilation du CSS et du JavaScript 
après chaque modification de code.

Lancez la commande suivante en parallèle du server de développement :

```bash
$ cd /chemin/vers/le/projet
$ npm run watch
```

[Suivant : 1.2 Utilisateurs de Docker](1-2-Utilisateurs-de-docker.md)
