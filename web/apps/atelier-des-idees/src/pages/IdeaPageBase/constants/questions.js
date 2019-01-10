riexport const FIRST_QUESTIONS = [
    {
        id: 'problem',
        label: 'Constat',
        question: 'Quel problème souhaitez vous résoudre ?',
        canCollapse: false,
        placeholder: 'Expliquez le problème que vous identifiez et espérez pouvoir remédier.',
    },
    {
        id: 'solution',
        label: 'Solution',
        question: 'Quelle réponse votre idée apporte-t-elle ?',
        canCollapse: false,
        placeholder: 'Expliquez comment votre proposition répond concrètement au problème.',
    },
];

export const SECOND_QUESTIONS = [
    {
        id: 'comparison',
        label: 'Comparaison',
        question: 'Cette proposition a-t-elle déjà été mise en oeuvre ou étudiée ?',
        canCollapse: true,
    },
    {
        id: 'impact',
        label: 'Impact',
        question: 'Cette proposition peut elle avoir des effets négatifs pour certains publics ?',
        canCollapse: true,
    },
    {
        id: 'right',
        label: 'Droit',
        question: 'Votre idée suppose t-elle de changer le droit ?',
        canCollapse: true,
        placeholder:
            'Expliquez si votre idée nécessite - ou non - de changer le droit en vigueur. Si oui, idéalement, précisez ce qu’il faudrait changer.',
    },
    {
        id: 'budget',
        label: 'Budget',
        question: 'Votre idée a-t-elle un impact financier ?',
        canCollapse: true,
    },
    {
        id: 'environment',
        label: 'Environnement',
        question: 'Votre idée a t-elle un impact écologique ?',
        canCollapse: true,
    },
    {
        id: 'parity',
        label: 'Égalité hommes-femmes',
        question: 'Votre idée a t-elle un impact sur l’égalité entre les femmes et les hommes ?',
        canCollapse: true,
    },
];
