Feature: Bannière consent PostHog
    En tant que visiteur non-connecté, je vois une bannière au 1er boot si
    aucun cookie consent n'est posé. Une fois ma décision prise, la bannière
    disparaît et le cookie est scopé au root-domain de ma marque.

    Scenario: Bannière affichée sur utilisateur.parti-renaissance.fr si cookie absent
        When je visite "https://utilisateur.parti-renaissance.fr/"
        Then je devrais voir "Analyse d'usage anonymisée"
        And la réponse devrait avoir le status 200

    Scenario: Bannière cachée si cookie pr_consent déjà posé
        Given j'ai le cookie "pr_consent" avec valeur "1"
        When je visite "https://utilisateur.parti-renaissance.fr/"
        Then je ne devrais pas voir "Analyse d'usage anonymisée"

    Scenario: Migration idempotente ap_consent (déjà en prod attalpresident.fr)
        Given j'ai le cookie "ap_consent" avec valeur "0"
        When je visite "https://utilisateur.attalpresident.fr/"
        Then le cookie "ap_consent" devrait toujours avoir la valeur "0"
        And je ne devrais pas voir "Analyse d'usage anonymisée"
