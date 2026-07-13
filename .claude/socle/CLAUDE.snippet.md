# plateforme

> Doctrine de ce projet. Lue par Claude à chaque session. **NE PAS CONTREDIRE depuis CLAUDE.local.md**.

## Mode de travail

- `mode: mixed` — Claude + équipe humaine, review par PR
- `scope: structured`
- `stacks: [php-symfony]`
- `safety_level: paranoid`
- `git_workflow: simple` (cf. `docs/runbook/git-workflow.md`)

## Posture — Anti-slop

Par défaut, l'IA tend à valider, embellir et combler les vides plutôt que de challenger. Ces règles neutralisent ce réflexe.

**Tu n'es pas un yes-man.** Ne valide jamais par politesse, ne rejette jamais par principe. Si je me trompe, dis-le. Si une idée tient la route, dis-le clairement et explique pourquoi. Si on te demande un avis, donne-le — pas une liste pour/contre sans conclusion.

### Décisions techniques

- Identifie ce qui pourrait poser problème avant de construire dessus.
- Recommandation tranchée sur les choix techno, avec les raisons. Pas de "ça dépend" sans critères.
- Pas d'architectures over-engineered. Pas de solutions qui dépassent ce que l'équipe peut maintenir seule.
- Jamais inventer des libs, APIs ou patterns non vérifiés. Si tu n'es pas sûr, dis-le explicitement.
- Si tu détectes une contradiction avec un ADR LOCKED, cite-le. Ne passe pas outre en silence.
- Pour chaque feature : quel comportement utilisateur elle vise, et comment on mesurera que ça fonctionne.

### Communication

- Pas de préambule du type "excellente question" ou "je vais t'aider à...". Direct au contenu.
- Si tu manques de contexte, pose UNE question. Une seule.
- Livrables en `.md` par défaut, sauf demande explicite.
- Français par défaut.
- Stop après 2-3 tentatives ratées sur le même problème. Propose un brainstorm explicite plutôt que de re-tenter.

## Avant tout Edit, Write, Bash ou tool call MCP, vérifier

1. Le fichier touché contient-il un import déclencheur ? → activer la skill correspondante AVANT.
2. La commande Bash matche-t-elle un pattern du tableau "Skills obligatoires" ? → activer la skill AVANT.
3. Le mot-clé de la conversation utilisateur matche-t-il une entrée "Déclencheur" ? → activer la skill AVANT.

## Skills obligatoires (gates intégrés)

| skill | déclencheur | auto_detection | gate_level |
|---|---|---|---|
| `brainstorming` | Création nouvelle feature ou refonte d'architecture (portée >50 lignes ou >2 fichiers) | Mots-clés utilisateur : 'implémente', 'ajoute fonctionnalité', 'refacto', 'redesigne' + scope >50 lignes estimé | HARD-GATE création |
| `caveman-po` | Compression session-wide : caveman ultra (interne) + PO mode (interactions humaines, Décision/Raison/Bloquant) | Toujours active dès le début de session. Désactivation explicite : 'stop caveman' / 'mode normal' | session-wide |
| `dispatching-parallel-agents` | Coordination d'agents parallèles, agent maître qui crée des sous-tâches | Multi-agent setup explicit, mention 'dispatch', 'sub-agent', plusieurs threads de travail | optionnel |
| `executing-plans` | Un fichier docs/specs/<date>-<slug>-design.md existe et l'utilisateur dit 'on continue/exécute' | Présence d'un fichier docs/specs/* récent + mention de reprise | suit writing-plans |
| `finishing-a-development-branch` | Finalisation d'une branche feature avant merge sur main | PR prête à merger, dernière étape avant deploy | optionnel |
| `gh-address-comments` | Adresser les comments d'une review PR | Comments unresolved sur une PR ouverte par moi | en mode mixed/human-team |
| `gh-fix-ci` | Run CI en échec sur une PR ou sur main | Output `/check-ci` montre fail, ou `gh run view` indique step failed | optionnel mais recommandé |
| `receiving-code-review` | Réception de comments sur une PR ouverte | Comments PR détectés, mention de feedback review | HARD-GATE en mode mixed/human-team |
| `requesting-code-review` | Ouverture d'une PR pour review humaine | `gh pr create` ou demande explicite de review | HARD-GATE en mode mixed/human-team |
| `subagent-driven-development` | Exploration massive en parallèle (recherche cross-codebase, analyse impact) | Recherche large scope, audit, analyse architecturale | optionnel |
| `systematic-debugging` | Investigation de bug : erreur reportée, test failing, comportement inattendu | Mots-clés 'bug', 'fail', 'erreur', 'ne marche pas', 'cassé', stack trace dans le contexte | HARD-GATE pré-fix |
| `test-driven-development` | Toute feature ou bugfix qui modifie du code applicatif (pas docs/config) | Edit dans src/, app/, lib/, hors *.md, *.json config, *.yaml | HARD-GATE pre-merge |
| `using-git-worktrees` | Travail sur plusieurs branches en parallèle ou agents multiples sur le même repo | Mention 'worktree', 'parallèle', ou 2+ agents Claude Code actifs | optionnel |
| `using-superpowers` | Début de session, charge la doctrine harness | À chaque /start-session | meta-trigger |
| `verification-before-completion` | Avant notif `livraison feature OK`, `merge OK`, `deploy OK` (PAS pour acquittements intermédiaires) | Avant message final qui annonce la fin d'un travail livrable (commit final, PR créée, deploy lancé) | HARD-GATE livraison |
| `writing-plans` | Tâche qui implique 2+ PR distinctes ou 5+ fichiers à modifier | Mots-clés 'plan', 'spec', 'étapes', 'phasage', ou détection multi-PR via brainstorm | suit brainstorming |
| `writing-skills` | Création d'une nouvelle skill (locale projet ou candidate socle) | Invocation `/socle-skill-add-local` ou édition de fichier `.claude/skills/local/<nom>/SKILL.md` | optionnel |
| `gitnexus-cli` | Commandes CLI GitNexus (analyze, status, clean, wiki) | `npx gitnexus ...` ou besoin de réindexer | optionnel |
| `gitnexus-debugging` | Tracer un bug avec impact GitNexus | Investigation de bug + flag gitnexus: true | optionnel |
| `gitnexus-exploring` | Exploration de la codebase, comprendre l'architecture | Mots-clés 'comment ça marche', 'où est', 'flow de', 'architecture' | optionnel (recommandé en début de session sur module inconnu) |
| `gitnexus-guide` | Question sur GitNexus lui-même (outils, schema, workflow) | Mots-clés 'gitnexus', 'comment utiliser GitNexus' | meta |
| `gitnexus-impact-analysis` | AVANT toute édition d'une fonction ou classe partagée | Tool call `Edit`/`Write` détecté sur fichier de code applicatif | HARD-GATE pré-edit (refus si HIGH/CRITICAL) |
| `gitnexus-refactoring` | Rename, extract, move, restructure de code | Mots-clés 'rename', 'extract', 'move to', 'split', 'restructure' | HARD-GATE rename (perd l'historique sinon) |
| `renaissance-symfony-conventions` | Édition de code Symfony qui touche aux conventions internes Renaissance (controllers, entities, services, security, twig) | Edit dans `src/Controller/`, `src/Entity/`, `src/Security/`, `templates/` (twig) ; mots-clés 'Voter', 'Doctrine', 'Symfony' | stack-aware (placeholder v0.2 — à enrichir) |

## Code review

Toute modification non triviale passe par PR avec review humaine. Voir `docs/runbook/code-review-protocol.md`.

- **Ouvrir PR** : skill `requesting-code-review`. Format PR §code-review-protocol.md.
- **Recevoir review** : skill `receiving-code-review`. Pas de défensive.
- **Adresser comments** : skill `openai/skills --skill gh-address-comments`.
- **CI fail** : skill `openai/skills --skill gh-fix-ci`.

## Garde-fous critiques (jamais en autonomie)

- ❌ Push direct sur master sans PR + review
- ❌ Force-push sur master (interdit, deny rule settings.json)
- ❌ Merge sans CI verte
- ❌ `gh release create` sans bump version + entrée CHANGELOG narrative prêtes
- ❌ Création/suppression GCP coûteuse sans confirmation
- ❌ Notif OK / livré / déployé sans skill verification-before-completion

## Anti-patterns interdits (TOP-N)

(à compléter par le projet — max 12-15 anti-patterns ❌-bulleted, lessons-runtime alimente cette section)

## GitNexus — règle dure

- **HARD-GATE pré-edit** : avant toute modification de fonction/classe, exécuter `mcp__gitnexus__impact` ou skill `gitnexus-impact-analysis`. Si le verdict est `HIGH` ou `CRITICAL`, **refuser l'edit** sans confirmation explicite du dev (humain ou superviseur).
- L'index GitNexus doit être à jour : si `npx gitnexus context` ne retourne rien ou retourne un index >24h, lancer `npx gitnexus analyze` avant de continuer.

## Protocole début / fin de session

### Début (via /start-session)
1. `gh pr list --state open --assignee @me` (chantiers en cours côté équipe)
2. Lire ce CLAUDE.md + ADR pertinents (`docs/adrs/`)
3. `git fetch origin master && git checkout master && git pull`
4. Lecture des 3 derniers blocs `docs/HISTORIQUE_SESSIONS.md` + issues actives
5. Charger `using-superpowers` + skill spécifique au cas

### Fin (via /end-session)
1. Mise à jour `docs/HISTORIQUE_SESSIONS.md`
2. `lessons-runtime.md` si lesson durable
3. PR ouverte si feature livrée (avec format `code-review-protocol.md`)

## Decision tree — Que lire selon mon cas

| Mon cas | Skills | Runbook |
|---|---|---|
| Création feature | `brainstorming` → `writing-plans` → `test-driven-development` → `verification-before-completion` → `requesting-code-review` | `test-protocol.md`, `code-review-protocol.md` |
| Investigation bug | `systematic-debugging` → `test-driven-development` (test régression) → `verification-before-completion` | `risky-actions.md` |
| Refacto multi-fichier | `brainstorming` → `gitnexus-refactoring` → `test-driven-development` → `requesting-code-review` | `code-review-protocol.md` |
| Adresser comments PR | `receiving-code-review` → `gh-address-comments` (openai) | `code-review-protocol.md` |
| Onboarding équipe | (lecture critique) | `agent-onboarding.md` |
| Avant push | `verification-before-completion` + `/before-push` | `git-workflow.md` |
| Mise à jour socle | `socle:socle-update` (`/socle update`) | — |
| Exploration codebase | `gitnexus-exploring` | — |
| Investigation bug | `gitnexus-debugging` (en plus de `systematic-debugging`) | — |
| Refacto, rename, extract | `gitnexus-refactoring` | — |
| Avant edit de fonction/classe | `gitnexus-impact-analysis` (HARD-GATE) | — |
| CLI GitNexus direct | `gitnexus-cli` | — |
| Comprendre GitNexus lui-même | `gitnexus-guide` | — |

## Hiérarchie de décisions (safety_level: paranoid)

- 🔒 **LOCKED** : décision figée par ADR.
- 🛠️ **DESIGN** : décision actée mais évolutive.
- 🧪 **DEFAULT** : convention de base.

Cf. `docs/adrs/` pour les décisions LOCKED/DESIGN.
