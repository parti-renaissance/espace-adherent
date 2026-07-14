Feature: Détection dynamique du site par hostname
    Le SiteDetector mappe chaque hostname à la marque correspondante.
    Fallback fail-open (return null) si hostname hors périmètre — évite
    de crasher admin/api/webhooks partageant le kernel Symfony.

    Scenario Outline: hostname → site correct
        When je visite "https://<hostname>/"
        Then la variable Twig globale "posthog_site" devrait être "<site>"

        Examples:
            | hostname                          | site               |
            | utilisateur.parti-renaissance.fr  | parti-renaissance  |
            | utilisateur.attalpresident.fr     | attalpresident     |
            | utilisateur.avecgabrielattal.fr   | avecgabrielattal   |
            | utilisateur.nouvellerepublique.fr | nouvellerepublique |

    Scenario: hostname hors périmètre PostHog n'est pas mappé (fail-open)
        When je visite "https://admin.attalpresident.fr/"
        Then la variable Twig globale "posthog_site" devrait être null
        And la réponse ne devrait PAS avoir le status 500
