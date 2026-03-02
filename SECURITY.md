# Politique de sécurité

## Versions supportées

Seule la version en cours sur la branche `master` reçoit des correctifs de sécurité.

| Branche         | Support          |
|-----------------|------------------|
| `master`        | ✅ Supportée      |
| Autres branches | ❌ Non supportées |

---

## Signaler une vulnérabilité

**Ne créez pas d'issue GitHub publique pour signaler une vulnérabilité de sécurité.**

Une divulgation publique prématurée pourrait exposer les utilisateurs à un risque avant qu'un correctif soit disponible.

### Comment nous contacter

Envoyez un e-mail à **`security@parti-renaissance.fr`** avec :

- Une description claire de la vulnérabilité
- Les étapes pour la reproduire
- L'impact estimé (données exposées, fonctionnalités affectées)
- Si possible, une suggestion de correctif

### Ce que vous pouvez attendre

| Étape                      | Délai estimé                     |
|----------------------------|----------------------------------|
| Accusé de réception        | sous 48h ouvrées                 |
| Évaluation initiale        | sous 5 jours ouvrés              |
| Correctif ou plan d'action | sous 30 jours pour les critiques |

Nous vous tiendrons informé de l'avancement et vous créditerons dans le changelog si vous le souhaitez.

---

## Périmètre

Ce dépôt couvre le back-end et l'API de **Renaissance Plateforme**. Pour l'app mobile [Renaissance App](https://github.com/parti-renaissance/espace-militant), signalez les vulnérabilités au même contact.

---

## Bonnes pratiques pour les contributeurs

Si vous découvrez accidentellement une vulnérabilité en contribuant :

1. **N'incluez pas** d'exploit ou de données sensibles dans votre PR
2. **Contactez-nous** à `security@parti-renaissance.fr` avant de soumettre
3. Nous coordonnerons ensemble le correctif et sa publication
