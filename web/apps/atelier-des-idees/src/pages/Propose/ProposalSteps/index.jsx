import React from 'react';
import Steps from './Steps';
import contributeImg from './../../../img/contribute_img_step.svg';
import proposeImg from './../../../img/propose_img_step.svg';
import voteImg from './../../../img/vote_img_step.svg';
import contributePicto from './../../../img/contribute_picto_step.svg';
import proposePicto from './../../../img/propose_picto_step.svg';
import votePicto from './../../../img/vote_picto_step.svg';

const steps = [
    {
        title: ['Publiez une nouvelle idée', <br />, 'sur notre plateforme'],
        picto: `${proposePicto}`,
        description: `Vous pouvez la rédiger seul(e) ou en comité. Si vous ne souhaitez pas la publier immédiatement, 
            vous pouvez enregistrer un brouillon. Une fois votre idée publiée vous disposez de 10 jours pour la modifier en prenant en compte les commentaires des autres marcheurs sur votre proposition.`,
        img: `${proposeImg}`,
        linkLabel: 'Je rédige',
        link: '/atelier-des-idees/creer-ma-proposition',
    },
    {
        title: ['Contribuez aux propositions', <br />, 'en cours de rédaction !'],
        picto: `${contributePicto}`,
        description:
            'Découvrez les propositions en cours de rédaction sur la plate-forme et partager vos commentaires pour aider les autres marcheurs à construire leur proposition. Vous pouvez même rechercher les contributions en fonction des domaines sur lesquels les rédacteurs ont signalé avoir besoin d\'aide !',
        img: `${contributeImg}`,
        linkLabel: 'Je contribue',
        link: '/atelier-des-idees/contribuer',
    },
    {
        title: ['Détaillez les modalités', <br />, 'du vote'],
        picto: `${votePicto}`,
        description:
            'Vous trouvez une idée particulièrement “essentielle”, “réalisable” ou “innovante” ? Votez avec l’une ou plusieurs de ces options !',
        img: `${voteImg}`,
        linkLabel: 'Je vote',
        link: '/atelier-des-idees/soutenir',
    },
];

class ProposalSteps extends React.PureComponent {
    componentDidMount() {
        window.scrollTo(0, 0);
    }
    render() {
        return (
            <div className="proposal--steps">
                <h2 className="proposal-steps__title">Pour participer, 3 possibilités :</h2>
                {steps.map((step, i) => (
                    <Steps key={i} {...step} />
                ))}
            </div>
        );
    }
}

export default ProposalSteps;
