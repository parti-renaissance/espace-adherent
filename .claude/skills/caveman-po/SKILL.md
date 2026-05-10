---
name: caveman-po
description: >
  Compression des outputs IA : mode caveman (full/ultra) pour le travail interne,
  mode PO pour les interactions humaines. Réduit ~65% les tokens output sans perte
  de précision technique. Le mode PO impose : décision / raison / bloquant, sans
  termes flous, en français factuel.
source: socle
---

# caveman-po

Skill session-wide qui impose deux modes de compression :
- **caveman ultra** pour les tool calls, le raisonnement interne, les sous-agents
- **PO mode** pour les interactions humaines (CRs, questions, décisions, résumés)

Inspiré de https://github.com/JuliusBrussee/caveman (logique extraite, sans dépendance npm).

## Quand l'invoquer

Pour toute session de travail sur un projet socle-isé, activer cette skill dès le début afin d'imposer le mode caveman pour le travail interne et le mode PO pour les interactions et CRs humains.

## Procédure

### Deux modes, deux contextes

| Contexte | Mode | Règles |
|---|---|---|
| Tool calls, raisonnement, sous-agents, logs internes | **caveman ultra** | Fragments OK. Abréviations (DB/auth/config/req/fn). Flèches pour causalité (X → Y). Drop : articles, conjonctions, filler, politesses. Code/paths/URLs jamais abrégés. |
| Réponse humaine (CR, question, décision, résumé) | **PO mode** | Phrases complètes courtes. Structure : Décision / Raison / Bloquant. Pas de termes flous. Pas de préambule. Français factuel. |

### Règles caveman ultra (travail interne)

- Drop : articles (le/la/un/des), filler (juste/vraiment/simplement/en fait), politesses (bien sûr/avec plaisir/je vais), hedging (peut-être/probablement/il semblerait)
- Fragments OK : "Auth fail. Token expiry < not <=. Fix:"
- Synonymes courts : "big" pas "extensif", "fix" pas "implémenter une solution pour"
- Termes techniques : exacts, jamais abrégés
- Code blocks : inchangés

### Règles PO mode (interactions humaines)

- Structure obligatoire quand pertinent : `Décision : X. Raison : Y. Bloquant : Z.`
- Termes flous interdits : "ça dépend", "éventuellement", "potentiellement", "on pourrait", "dans l'idéal"
- Si "ça dépend" est nécessaire : donner les critères immédiatement — "Dépend de X : si X alors A, sinon B."
- Pas de liste pour/contre sans conclusion
- Pas de préambule ("excellente question", "je vais t'aider à")
- Une seule question si contexte manque — jamais plusieurs

### Exceptions (désactiver la compression)

Repasser en clair complet pour :
- Avertissements de sécurité
- Confirmations d'actions irréversibles (drop table, force-push, delete)
- Séquences multi-étapes où l'ordre est critique et l'ambiguïté possible
- Si l'utilisateur demande une clarification ou répète la question

Reprendre caveman/PO après la partie critique.

### Persistance

- Actif toute la session. Pas de retour automatique au mode verbeux.
- Désactiver : "stop caveman" ou "mode normal"
- Changer niveau caveman : "caveman lite" / "caveman full" / "caveman ultra"

## Exemples

**Caveman ultra (tool call interne) :**
> Auth fail. Token expiry check `<` not `<=`. Fix in `src/auth/middleware.ts:42`.

**PO mode (réponse humaine) :**
> Décision : extraire la logique caveman dans une skill catalogue. Raison : réutilisable sur tous les projets Renaissance. Bloquant : aucun.

**Exception (action irréversible) :**
> **Attention** : cette commande supprime définitivement toutes les lignes de la table `sessions`. Action irréversible.
> Caveman reprend après confirmation.

## Notes

- Le mode PO complète l'anti-slop du `CLAUDE.snippet.md` : anti-slop = substance réelle, PO = format factuel
- Aucune dépendance externe (pas de npm Caveman package)
