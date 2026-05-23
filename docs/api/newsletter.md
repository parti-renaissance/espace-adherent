# Renaissance Newsletter API

API publique d'inscription à une newsletter Renaissance, en double opt-in.

## Vue d'ensemble

Le flux comporte deux étapes obligatoires :

1. Le client (site web, application, intégration serveur-à-serveur) envoie `POST /api/newsletter` avec l'adresse email et les métadonnées d'inscription. Le serveur répond `201 Created` et envoie un email de confirmation à l'utilisateur.
2. L'utilisateur clique sur le lien contenu dans l'email de confirmation. Ce clic finalise l'inscription côté serveur et redirige l'utilisateur vers une page de confirmation (dédiée au site appelant si configurée, sinon générique).

Tant que la deuxième étape n'a pas eu lieu, **aucune donnée n'est transmise en aval** (segmentation, envois marketing, etc.). Une inscription non confirmée reste en attente côté serveur et finit par être purgée.

## Prérequis

L'endpoint exige un **code de source** propre à chaque intégrateur. Ce code est attribué par l'équipe tech Renaissance et identifie l'origine de chaque inscription. Il configure également la page de confirmation vers laquelle l'utilisateur est redirigé après le clic sur le lien email.

Pour obtenir un code de source, transmettre à l'équipe tech :

- un nom lisible pour l'intégration (ex. `attalpresident.fr — campagne 2027`) ;
- l'URL HTTPS de la page de confirmation dédiée, si elle existe — sinon la page générique sera utilisée ;
- une adresse de contact technique.

Tant que le code n'est pas provisionné côté serveur, l'endpoint rejette toute requête avec ce code par un `400 Bad Request`.

## `POST /api/newsletter`

### Requête

| Élément        | Valeur               |
| -------------- | -------------------- |
| Méthode        | `POST`               |
| URL            | `https://<host>/api/newsletter` |
| Authentification | aucune             |
| `Content-Type` | `application/json`   |

Les clés du body sont en **`snake_case`**. Toute clé non listée dans le tableau ci-dessous est silencieusement ignorée à la désérialisation — par exemple `firstName` (camelCase), `id`, `created_at` sont reçus sans erreur mais ne sont pas pris en compte.

#### Exemple de body valide

```json
{
  "first_name": "Jane",
  "last_name": "Doe",
  "postal_code": "75001",
  "email": "jane.doe@example.com",
  "source": "ton_code_de_source",
  "cgu_accepted": true,
  "recaptcha": "<friendly-captcha-solution-token>"
}
```

#### Exemple de body minimal (champs requis uniquement)

```json
{
  "email": "jane.doe@example.com",
  "source": "ton_code_de_source",
  "cgu_accepted": true,
  "recaptcha": "<friendly-captcha-solution-token>"
}
```

#### Tableau des champs

| Champ          | Type     | Requis  | Validation serveur                                                                                                            | Comportement si absent ou `null`                              |
| -------------- | -------- | ------- | ----------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------- |
| `first_name`   | `string` | non     | Aucune.                                                                                                                       | Le prénom est laissé vide côté serveur.                       |
| `last_name`    | `string` | non     | Aucune.                                                                                                                       | Le nom est laissé vide côté serveur.                          |
| `postal_code`  | `string` | non     | **Aucune validation de format.** Le champ accepte n'importe quelle chaîne et est stocké tel quel (utile pour les inscriptions hors France où le format varie). Voir la note ci-dessous pour les recommandations côté intégrateur. | Le code postal est laissé vide côté serveur.                  |
| `email`        | `string` | **oui** | Format email valide (validation interne stricte, sans vérification DNS). Doit être unique parmi les inscriptions **déjà confirmées**. | `400 Bad Request` (`violations[].propertyPath = "email"`).    |
| `source`       | `string` | **oui** | Doit être un code de source provisionné et actif. Maximum 100 caractères.                                                     | `400 Bad Request` (`violations[].propertyPath = "source"`).   |
| `cgu_accepted` | `bool`   | **oui** | Doit valoir exactement `true`. Toute autre valeur (`false`, `null`, `1`, `"true"`, etc.) est rejetée.                          | `400 Bad Request` (`violations[].propertyPath = "cguAccepted"`). |
| `recaptcha`    | `string` | **oui** | Token Friendly Captcha valide et non consommé, vérifié côté serveur contre l'API Friendly Captcha. Un token absent, vide, invalide ou déjà consommé est rejeté. | `400 Bad Request` (`violations[].propertyPath = "recaptcha"`). |

> **Note sur `postal_code`** : aucune validation de format n'est appliquée côté serveur, intentionnellement, afin de supporter les codes postaux internationaux (US `90210-1234`, UK `SW1A 1AA`, NL `1234 AB`, etc.) ainsi que l'absence totale de code postal pour les inscriptions hors France. L'intégrateur reste libre de valider côté client si la qualité des données est critique pour son cas d'usage (segmentation géographique fine, par exemple).

### Réponses

| Code              | Corps                                                       | Signification                                                                                                                                                                |
| ----------------- | ----------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `201 Created`     | `"OK"`                                                      | L'inscription est enregistrée côté serveur et un email de confirmation a été envoyé. L'inscription reste **en attente** tant que l'utilisateur n'a pas cliqué sur le lien.   |
| `400 Bad Request` | JSON avec un tableau `violations[]` (format ci-dessous).    | Body invalide, captcha invalide ou expiré, email déjà inscrit et confirmé, ou source inconnue/désactivée.                                                                    |

#### Format du corps `400`

```json
{
  "violations": [
    {
      "propertyPath": "email",
      "title": "Description lisible de l'erreur."
    }
  ]
}
```

Le champ `propertyPath` désigne le champ en faute, en **camelCase** (ex. `email`, `postalCode`, `cguAccepted`, `recaptcha`, `source`). Plusieurs violations peuvent être retournées simultanément.

#### Cas particuliers de `400`

| Cas                                              | Détail                                                                                                                                                                                                          |
| ------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Email déjà inscrit **et confirmé**                | La requête est rejetée avec une violation générique sur `email` (pas de message « déjà inscrit » dans la réponse, par doctrine de vie privée — voir les recommandations UX ci-dessous).                          |
| Email déjà inscrit **mais non confirmé**          | La requête répond `201`. La source est mise à jour si elle a changé, et un nouvel email de confirmation est envoyé. Permet de relancer une inscription en attente, par exemple depuis un autre site Renaissance.|
| Source inconnue                                  | Violation sur `source`.                                                                                                                                                                                         |
| Source provisionnée mais désactivée par l'équipe tech | Violation sur `source`.                                                                                                                                                                                         |
| `recaptcha` consommé (déjà soumis une fois)      | Violation sur `recaptcha`. Un token Friendly Captcha est à usage unique — chaque nouvelle requête doit fournir un nouveau token.                                                                                |

## Recommandations UX côté client

- **Ne jamais révéler à l'utilisateur si son adresse est déjà inscrite.** Sur un `400`, afficher un message neutre du type « Si cette adresse est valide, un email de confirmation vous sera envoyé dans quelques instants. » Ce phrasing couvre à la fois le cas du doublon et le cas de l'erreur de saisie sans leak de présence en base.
- Sur un `201`, inviter l'utilisateur à vérifier sa boîte mail **y compris le dossier spam/indésirables** pour cliquer sur le lien de confirmation.
- Ne **pas** retenter automatiquement en cas de `400` : présenter une erreur générique ou log silencieux côté intégrateur. Un retry automatique risque d'épuiser des tokens Friendly Captcha valides et de polluer les métriques.
- Pour les utilisateurs sans code postal français (Français de l'étranger, intégrations internationales), laisser le champ vide ou envoyer leur code postal local tel quel — le serveur l'accepte sans validation.

## Friendly Captcha

L'endpoint exige un token Friendly Captcha dans le champ `recaptcha`. C'est la protection anti-bot de l'endpoint — sans token valide, la requête est rejetée.

### Site key

```
FCMUUBPHUHST12CT
```

Cette clé est publique et peut être utilisée depuis n'importe quel contexte client (site web, SPA, application mobile, intégration serveur-à-serveur).

### Flux côté client (browser, SPA)

1. Intégrer le widget Friendly Captcha dans la page avec `data-sitekey="FCMUUBPHUHST12CT"`. Voir la [documentation officielle Friendly Captcha](https://docs.friendlycaptcha.com/).
2. Laisser le widget résoudre le challenge proof-of-work (automatique par défaut, sans interaction utilisateur).
3. Récupérer la valeur de l'input `frc-captcha-solution` généré par le widget une fois le challenge résolu.
4. Envoyer cette valeur dans le champ `recaptcha` du body JSON de la requête.

### Flux serveur ou intégration programmatique

Utiliser le [SDK Friendly Captcha](https://docs.friendlycaptcha.com/) approprié pour résoudre un challenge côté serveur, puis transmettre le token dans la requête.

## Exemple cURL

```bash
curl -X POST https://<host>/api/newsletter \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Jane",
    "last_name": "Doe",
    "postal_code": "75001",
    "email": "jane.doe@example.com",
    "source": "ton_code_de_source",
    "cgu_accepted": true,
    "recaptcha": "<token>"
  }'
```

## Questions fréquentes

### Est-ce que je peux tester avec un email que j'ai déjà inscrit ?

Oui, mais la requête sera rejetée avec `400` si cet email est déjà confirmé. Pour des tests répétés, utiliser des alias (`prefix+test1@example.com`, `prefix+test2@example.com`, etc.). La plupart des fournisseurs mail traitent ces alias comme une seule boîte de réception.

### Combien de temps l'utilisateur a-t-il pour confirmer son inscription ?

Il n'y a pas d'expiration stricte côté client, mais les inscriptions non confirmées sont purgées périodiquement côté serveur. Inviter l'utilisateur à confirmer dans la foulée de l'inscription pour éviter toute ambiguïté.

### Est-ce que je reçois un callback quand l'utilisateur a cliqué sur le lien de confirmation ?

Non, il n'y a pas de webhook de confirmation. Pour mesurer le taux de confirmation, passer un paramètre de tracking dans l'URL de la page de confirmation fournie lors de l'onboarding (par exemple `?utm_source=newsletter_confirmation`), et le récupérer côté frontend quand l'utilisateur atterrit sur cette page.

### Comment désactiver temporairement un code de source ?

Contacter l'équipe tech Renaissance. La désactivation est immédiate : elle bloque toute nouvelle inscription utilisant ce code mais n'empêche pas les utilisateurs déjà inscrits de confirmer un lien email reçu avant la désactivation.

### Pourquoi `postal_code` n'est pas validé côté serveur ?

Par choix : valider strictement le format français exclut de facto les Français de l'étranger ainsi que toute intégration internationale. La validation est laissée à l'intégrateur, qui connaît son audience et peut décider du compromis acceptable entre permissivité (acquisition maximale) et qualité des données (segmentation géographique). Si Renaissance décide à l'avenir de resserrer la validation côté serveur, ce sera signalé via une PR sur ce document.
