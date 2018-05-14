import { formatNb } from './../utils';

const data = [
    { name: 'Janvier', adherent: 9000, adherentMembre: 7200, amt: 900 },
    { name: 'Fevrier', adherent: 10000, adherentMembre: 8000, amt: 2400 },
    { name: 'Mars', adherent: 7020, adherentMembre: 6400, amt: 2400 },
    { name: 'Avril', adherent: 7000, adherentMembre: 4000, amt: 2400 },
    { name: 'Mai', adherent: 1020, adherentMembre: 400, amt: 2400 },
    { name: 'Juin', adherent: 6341, adherentMembre: 2400, amt: 2400 },
];

export default data;

export const fakeNb = () => formatNb(Math.floor(Math.random() * Math.floor(400000)));
