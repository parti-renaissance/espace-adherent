# plateforme

> Doctrine de ce projet, gérée par socle. Voir `.claude/socle/CLAUDE.snippet.md` pour la version générée.

@.claude/socle/CLAUDE.snippet.md

## Notes spécifiques au projet

<!-- Ajoute ici tout ce qui est propre au projet et qui ne mérite pas un ADR formel.
     La doctrine commune (mode, scope, stacks, skills, garde-fous, anti-patterns, decision tree)
     est dans .claude/socle/CLAUDE.snippet.md, régénéré par socle-init.

     Ci-dessous : contenu pré-migration de CLAUDE.md (bloc gitnexus auto-géré). À curer
     post-merge — duplique partiellement le tableau "Decision tree GitNexus" du snippet. -->

<!-- gitnexus:start -->
# GitNexus — Code Intelligence

This project is indexed by GitNexus as **espace-adherent** (39177 symbols, 104562 relationships, 300 execution flows). Use the GitNexus MCP tools to understand code, assess impact, and navigate safely.

> If any GitNexus tool warns the index is stale, run `npx gitnexus analyze` in terminal first.

## Always Do

- **MUST run impact analysis before editing any symbol.** Before modifying a function, class, or method, run `gitnexus_impact({target: "symbolName", direction: "upstream"})` and report the blast radius (direct callers, affected processes, risk level) to the user.
- **MUST run `gitnexus_detect_changes()` before committing** to verify your changes only affect expected symbols and execution flows.
- **MUST warn the user** if impact analysis returns HIGH or CRITICAL risk before proceeding with edits.
- When exploring unfamiliar code, use `gitnexus_query({query: "concept"})` to find execution flows instead of grepping. It returns process-grouped results ranked by relevance.
- When you need full context on a specific symbol — callers, callees, which execution flows it participates in — use `gitnexus_context({name: "symbolName"})`.

## Never Do

- NEVER edit a function, class, or method without first running `gitnexus_impact` on it.
- NEVER ignore HIGH or CRITICAL risk warnings from impact analysis.
- NEVER rename symbols with find-and-replace — use `gitnexus_rename` which understands the call graph.
- NEVER commit changes without running `gitnexus_detect_changes()` to check affected scope.

## Resources

| Resource | Use for |
|----------|---------|
| `gitnexus://repo/espace-adherent/context` | Codebase overview, check index freshness |
| `gitnexus://repo/espace-adherent/clusters` | All functional areas |
| `gitnexus://repo/espace-adherent/processes` | All execution flows |
| `gitnexus://repo/espace-adherent/process/{name}` | Step-by-step execution trace |

## CLI

| Task | Read this skill file |
|------|---------------------|
| Understand architecture / "How does X work?" | `.claude/skills/gitnexus/gitnexus-exploring/SKILL.md` |
| Blast radius / "What breaks if I change X?" | `.claude/skills/gitnexus/gitnexus-impact-analysis/SKILL.md` |
| Trace bugs / "Why is X failing?" | `.claude/skills/gitnexus/gitnexus-debugging/SKILL.md` |
| Rename / extract / split / refactor | `.claude/skills/gitnexus/gitnexus-refactoring/SKILL.md` |
| Tools, resources, schema reference | `.claude/skills/gitnexus/gitnexus-guide/SKILL.md` |
| Index, status, clean, wiki CLI commands | `.claude/skills/gitnexus/gitnexus-cli/SKILL.md` |
| Work in the Entity area (875 symbols) | `.claude/skills/generated/entity/SKILL.md` |
| Work in the Repository area (480 symbols) | `.claude/skills/generated/repository/SKILL.md` |
| Work in the Command area (206 symbols) | `.claude/skills/generated/command/SKILL.md` |
| Work in the OAuth area (190 symbols) | `.claude/skills/generated/oauth/SKILL.md` |
| Work in the Renaissance area (182 symbols) | `.claude/skills/generated/renaissance/SKILL.md` |
| Work in the Admin area (178 symbols) | `.claude/skills/generated/admin/SKILL.md` |
| Work in the Handler area (165 symbols) | `.claude/skills/generated/handler/SKILL.md` |
| Work in the ORM area (158 symbols) | `.claude/skills/generated/orm/SKILL.md` |
| Work in the Jecoute area (157 symbols) | `.claude/skills/generated/jecoute/SKILL.md` |
| Work in the VotingPlatform area (144 symbols) | `.claude/skills/generated/votingplatform/SKILL.md` |
| Work in the Event area (139 symbols) | `.claude/skills/generated/event/SKILL.md` |
| Work in the NationalEvent area (133 symbols) | `.claude/skills/generated/nationalevent/SKILL.md` |
| Work in the Pap area (129 symbols) | `.claude/skills/generated/pap/SKILL.md` |
| Work in the AdherentMessage area (128 symbols) | `.claude/skills/generated/adherentmessage/SKILL.md` |
| Work in the Mailchimp area (108 symbols) | `.claude/skills/generated/mailchimp/SKILL.md` |
| Work in the EnMarche area (103 symbols) | `.claude/skills/generated/enmarche/SKILL.md` |
| Work in the Api area (103 symbols) | `.claude/skills/generated/api/SKILL.md` |
| Work in the Designation area (101 symbols) | `.claude/skills/generated/designation/SKILL.md` |
| Work in the Geo area (98 symbols) | `.claude/skills/generated/geo/SKILL.md` |
| Work in the Listener area (92 symbols) | `.claude/skills/generated/listener/SKILL.md` |

<!-- gitnexus:end -->
