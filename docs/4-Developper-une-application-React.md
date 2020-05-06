# 4. Développer une application React

[Précédent : 3. Processus de développement](3-Processus-de-developpement.md)

Il est possible grâce à un système de délégation de Symfony de créer des applications React
classiques (create-react-app) au sein de l'application Symfony En Marche.
Ce document décrit les quelques étapes requises pour cela.

N'hésitez pas à inspecter l'application d'exemple (`web/apps/example`) pour mieux comprendre
les propos de ce document si nécessaire.

## Créer l'application

Une application React intégrée dans le projet En Marche doit être créée avec le format standard
`create-react-app`: https://github.com/facebook/create-react-app.

Elle doit être créée dans un dossier au sein de `web/apps` et posséder un nom unique et explicite
(par exemple : "projets-citoyens").

## Processus de développement

Il y a deux façons possibles de développer votre application React. Vous pouvez :

- soit utiliser le template simple `web/apps/example/public/index.html` qui vous fournira des
  éléments de base au sein des quels vous pourrez développer votre application normalement.
- soit développer en intégrant directement votre application dans Symfony en local (cela requiert
  d'avoir le projet Symfony fonctionnel localement).
  
Que ce soit d'une façon ou d'une autre, rappelez-vous des quelques spécificités suivantes par rapport
à une application React habituelle :

- Vous devez ajouter la clé de configuration `homepage` dans votre package.json pour localiser correctement
  les assets: `"homepage" : "/apps/<dossier-app>/build",`.
- Votre application devra toujours être rendered dans un container d'ID `#root`.
- Depuis votre application, vous aurez accès à une variable globale `config` qui est un objet contenant deux clés :

```js
var config = {
    env: 'prod', // Environnement d'exécution actuel
    staging: true, // false si l'application est actuellement sur staging (ou en local), false sinon
};
``` 

## Intégration dans Symfony

Si vous souhaitez développer une application en l'intégrant directement dans Symfony, ou que vous souhaitez tester
votre application dans le contexte de Symfony, il vous faut d'abord installer le projet en local. N'hésitez
pas à lire la documentation associée (premier chapitre de ce dossier).

Pour intégrer votre application auprès de Symfony, vous devez effectuer deux choses :

### 1. Créer une classe PHP pour votre application

Dans le dossier `src/React/App`, créez une nouvelle classe (en suivant bien la convention de nommage des autres
applications) selon le modèle suivant :

```php
<?php

namespace App\React\App;

use App\React\ReactAppInterface;
use Symfony\Component\Routing\Route;

class MyReactApp implements ReactAppInterface
{
    public function getTitle(): string
    {
        // Indiquez ici le nom de votre application tel qu'il doit être affiché dans le tag HTML <title>
        return 'Mon application';
    }

    public function getDirectory(): string
    {
        // Indiquez ici le nom du dossier dans web/apps
        return 'my-react-app'; 
    }

    public function enableInProduction(): bool
    {
        // Rentournez false si votre application doit être deployée uniquement sur staging pour le moment.
        return true;
    }

    public function getRoutes(): array
    {
        // Listez ici toutes les URLs que votre application React utilise afin qu'elles soient bien redirigées
        // via Symfony vers votre application.
        // Le nom associée à chaque URL sera utilisable pour générer des URLs vers votre application au sein de
        // Symfony/Twig.
        return [
            'home' => new Route('/my-react-app'),
            'search' => new Route('/my-react-app/recherche'),
        ];
    }
}
```

Une fois la classe créée, ajoutez-la dans la liste des applications dans le fichier `src/React/ReactAppRegistry.php`:

```php
<?php

// ...

class ReactAppRegistry
{
    // ...

    public function __construct()
    {
        // Là aussi, le nom associée à votre application sera utilisable pour générer des URLs au sein de Symfony/Twig.
        $this->apps = [
            'citizen_projects' => new CitizenProjectApp(),
            'my_app' => new MyReactApp(),
        ];
    }
    
    // ...
}
```

Une fois cela effectué, vous devriez pouvoir avoir accès à votre application depuis Symfony.
