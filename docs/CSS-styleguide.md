# CSS Styleguide

## La typographie

### Tailles:
- `.text--small` (12px)
- `.text--medium-small` (18px)
- `.text--medium` (24px)
- `.text--large` (34px)
- `.text--extra-large` (48px)

### Poids:
- `.text--normal` (400)
- `.text--bold` (700)

### Styles:
- `.text--italic`
- `.text--all-caps`
- `.text--center`
- `.text--limited`

### Couleurs:
- `.text--white`
- `.text--on-pink`
- `.text--on-white`

### Modulaires:
- `.text--summary` (card summary)

### SVG/icons et texte

Exemple d'un SVG à gauche d'un ou deux mots:

```
<div class="icon--with-text">
    <svg>[mon svg]</svg>
    Connexion
</div>
```

### Listes de liens

#### Un exemple "column"

Il faudra bien utiliser la classe `.list__links--col`.

```
<ul class="list__links list__links--col list__links--no-decor">
    <li><h4><a href="#" class="text--bold text--medium-small">S’engager</a></h4></li>
    <li><a href="#">Adhérer</a></li>
    <li><a href="#">Donner</a></li>
    <li><a href="#">Rejoindre un comité</a></li>
    <li><a href="#">S’inscrire à un événement</a></li>                    
    <li><a href="#">FAQ</a></li>                    
</ul>
```

#### Un exemple "row"

Il faudra bien utiliser la classe `.list__links--row`.

```
<ul class="list__links list__links--row list__links--no-decor">
    <li>En Marche &copy;</li>
    <li><a href="#">Écrivez-nous</a></li>
    <li><a href="#">Presse</a></li>
    <li><a href="#">Mentions Legales</a></li>
</ul>
```

### Un exemple avec des SVGs

Il faudra bien utiliser la classe `.list__links--svgs`.

```
<ul class="list__links list__links--row list__links--no-decor list__links--svgs">
    <li class="head">Suivez En Marche !</li>
    <li><a href="https://www.facebook.com/EnMarche/"><i class="fa fa-facebook-square"></i></a></li>
    <li><a href="https://twitter.com/enmarchefr"><i class="fa fa-twitter"></i></a></li>
    <li><a href="https://www.instagram.com/enmarchefr/"><i class="fa fa-instagram"></i></a></li>
    <li><a href="https://www.youtube.com/channel/UCJw8np695wqWOaKVhFjkRyg"><i class="fa fa-youtube-square"></i></a></li>
    <li><a href="https://medium.com/@enmarchefr"><i class="fa fa-medium"></i></a></li>
    <li><a href="https://www.linkedin.com/company/en-marche"><i class="fa fa-linkedin-square"></i></a></li>
    <li><a href="#"><i class="fa fa-snapchat-ghost"></i></a></li>
</ul>
```


### En bonus
- `.head` (row) et `h4` (column) pour créer des têtes de listes
- `.list__links--no-decor` permet d'enlever les decorations de liens
