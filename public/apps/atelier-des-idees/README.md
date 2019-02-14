# Atelier des idées - App React

Projet créé avec [Create React App](https://github.com/facebookincubator/create-react-app) (dernière version du guide d'utilisation [ici](https://github.com/facebookincubator/create-react-app/blob/master/packages/react-scripts/template/README.md)).

## Installation et utilisation

### Installation

`yarn install`

### Lancement

`yarn start`

Aller sur [http://localhost:3000](http://localhost:3000)

### Storybook

`yarn storybook`

Aller sur [http://localhost:9009](http://localhost:9009)

## Simuler une connexion utilisateur

Dans `/src/redux/reducers/auth.js` (voir les [specs](https://github.com/EnMarche/en-marche.fr/blob/adi-react/features/api/adherents.feature#L128) pour plus de détails sur la structure de l'objet `user`) :

```
// Comment initial state to override
// const initialState = {
//     isAuthenticated: false,
//     user: {},
// };

// and add the custom initial state
const initialState = {
    isAuthenticated: true,
    user: { uuid: '0000', firstName: 'Jean-Pierre', lastName: 'Français' },
};
```
