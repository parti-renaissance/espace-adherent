# Renaissance Newsletter API

Endpoint public pour inscrire une adresse email à une newsletter Renaissance.

Le flux se déroule en deux temps :

1. L'utilisateur soumet ses informations → `POST /api/newsletter` répond `201`.
2. Il reçoit un email contenant un lien de confirmation. Le clic finalise l'inscription et le redirige vers une page de confirmation dédiée à ton site (configurée par l'équipe Renaissance lors de l'onboarding), ou vers une page générique par défaut.

Sans le clic de confirmation, l'inscription reste en attente et **aucune** donnée n'est transmise en aval. C'est un double opt-in systématique.

---

## Prérequis d'intégration

Avant de pouvoir utiliser l'endpoint, il faut obtenir un **code de source** auprès de l'équipe tech Renaissance. Ce code est un identifiant technique (ex. `site_eu_2026`) qui :

- Trace l'origine de chaque inscription,
- Définit la page de confirmation vers laquelle l'utilisateur sera redirigé après avoir cliqué sur le lien reçu par email.

Fournis à l'équipe tech :
- Un nom lisible pour ton site,
- L'URL HTTPS de la page de confirmation (si tu en as une dédiée),
- Une adresse de contact technique.

Tu recevras en retour ton `code`. Tant que ce code n'a pas été provisionné, l'endpoint refusera tes requêtes avec un `400`.

---

## `POST /api/newsletter`

### Requête

- **Méthode** : `POST`
- **URL** : `https://<host>/api/newsletter`
- **Auth** : aucune
- **Content-Type** : `application/json`

> ⚠ Les clés du body sont en **snake_case**. Envoyer `firstName` au lieu de `first_name` ne produira pas d'erreur mais le champ sera ignoré.

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

| Champ | Type | Requis | Description |
|---|---|---|---|
| `first_name` | string | non | Prénom |
| `last_name` | string | non | Nom |
| `postal_code` | string | **oui** | Code postal |
| `email` | string | **oui** | Adresse email valide (format RFC) |
| `source` | string | **oui** | Code de source fourni par l'équipe tech (voir prérequis) |
| `cgu_accepted` | bool | **oui** | Doit valoir `true` — l'utilisateur doit avoir accepté les CGU |
| `recaptcha` | string | **oui** | Token de solution Friendly Captcha (voir section dédiée) |

### Réponses

| Code | Corps | Signification |
|---|---|---|
| `201 Created` | `"OK"` | L'inscription est enregistrée et un email de confirmation vient d'être envoyé à l'adresse fournie. |
| `400 Bad Request` | JSON avec un tableau `violations[]` (voir format ci-dessous) | Body invalide, captcha invalide ou expiré, ou combinaison champs/source non acceptée. |

Format du corps pour un `400` :

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

Le champ `propertyPath` indique le champ en faute. Plusieurs violations peuvent être retournées simultanément.

### Recommandations UX côté client

- **Ne jamais révéler à l'utilisateur si son adresse est déjà inscrite.** En cas de `400`, affiche un message neutre du type « Si cette adresse est valide, tu vas recevoir un email de confirmation dans quelques instants. » — ça préserve la vie privée et couvre le cas du doublon sans le leak.
- Côté `201`, le message doit inviter l'utilisateur à vérifier sa boîte mail (y compris les spams) pour finaliser l'inscription.
- Ne pas retenter automatiquement en cas de `400`. Présenter l'erreur à l'utilisateur ou log silencieux côté intégrateur.

---

## Friendly Captcha

L'endpoint exige un token de solution Friendly Captcha dans le champ `recaptcha`. C'est la protection anti-bot de l'endpoint.

### Site key

```
FCMUUBPHUHST12CT
```

Cette clé peut être utilisée depuis n'importe quel contexte client (site web, SPA, application mobile, intégration serveur-à-serveur).

### Flux côté client (browser / SPA)

1. Intégrer le widget Friendly Captcha dans ta page avec `data-sitekey="FCMUUBPHUHST12CT"`. Voir la [documentation officielle Friendly Captcha](https://docs.friendlycaptcha.com/).
2. Laisser le widget résoudre le challenge (automatique par défaut).
3. Récupérer la valeur de l'input `frc-captcha-solution` généré par le widget.
4. L'envoyer dans le champ `recaptcha` du body JSON.

### Flux côté serveur / intégration programmatique

Utiliser le [SDK Friendly Captcha](https://docs.friendlycaptcha.com/) approprié pour résoudre un challenge côté serveur, puis transmettre le token dans la requête.

---

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

---

## Questions fréquentes

**Est-ce que je peux tester avec un email que j'ai déjà inscrit ?**
Oui, mais la requête sera rejetée avec `400`. Utilise une adresse différente pour chaque test. Pour des tests répétés, pense à utiliser des alias (`prefix+test1@example.com`, `prefix+test2@example.com`, etc.).

**Combien de temps l'utilisateur a-t-il pour confirmer son inscription ?**
Il n'y a pas d'expiration stricte côté client, mais les inscriptions non confirmées sont purgées périodiquement côté serveur. Demande à l'utilisateur de confirmer dans la foulée de l'inscription pour éviter toute ambiguïté.

**Est-ce que je reçois un callback quand l'utilisateur a cliqué sur le lien de confirmation ?**
Non. Si tu as besoin de savoir qu'un utilisateur a confirmé, le plus simple est de passer un paramètre de tracking dans l'URL de confirmation que tu as fournie à l'équipe tech lors de l'onboarding, et de le récupérer quand l'utilisateur atterrit sur ta page.

**Comment désactiver temporairement un code de source ?**
Contacter l'équipe tech Renaissance. La désactivation est immédiate et n'empêche pas les utilisateurs déjà inscrits de confirmer leur email, mais bloque les nouvelles inscriptions avec ce code.
