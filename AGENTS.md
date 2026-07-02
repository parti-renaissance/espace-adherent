# plateforme — Instructions pour les agents IA

Ces instructions s'appliquent à tous les agents IA travaillant sur ce repo (Claude, Codex, Aider, Cursor, etc.).

La doctrine complète vit dans **[`CLAUDE.md`](CLAUDE.md)** (et son snippet généré `.claude/socle/CLAUDE.snippet.md`). Toute mise à jour des conventions IA doit s'y faire pour rester cohérente entre tous les agents.

Skills externes : versionnées dans `.agents/skills/` (universal layout, lisible par Codex/Cursor/etc.) + symlinks `.claude/skills/` pour Claude Code. Lock file natif : `skills-lock.json`. Vue socle : `.claude/socle/skills-installed.yaml`.
