import React from 'react';
import Steps from './Steps';
import contribute_img from './../../../img/contribute_img_step.svg';
import propose_img from './../../../img/propose_img_step.svg';
import vote_img from './../../../img/vote_img_step.svg';
import contribute_picto from './../../../img/contribute_picto_step.svg';
import propose_picto from './../../../img/propose_picto_step.svg';
import vote_picto from './../../../img/vote_picto_step.svg';

const steps = [
    {
        title: [
            <h3>
                Publiez une nouvelle idée <br />
                sur notre plateforme
            </h3>,
        ],
        picto: `${propose_picto}`,
        description: `Vous pouvez la rédiger seul(e) ou en comité. Si vous ne souhaitez pas la publier immédiatement, 
            vous pouvez enregistrer un brouillon. Attention: une fois votre idée publiée vous disposez de 10 jours pour la modifier en prenant en compte les commentaires des autres marcheurs sur votre proposition.`,
        img: `${propose_img}`,
        linkLabel: 'Je rédige',
        link: '/atelier-des-idees/proposer',
    },
    {
        title: [
            <h3>
                Contribuez aux propositions <br />
                en cours de rédaction !
            </h3>,
        ],
        picto: `${contribute_picto}`,
        description:
            'Découvrez les propositions en cours de rédaction sur la plate-forme et partager vos commentaires pour aider les autres marcheurs à construire leur proposition. Vous pouvez même rechercher les contributions en fonction des domaines sur lesquels les rédacteurs ont signalé avoir besoin d\'aide !',
        img: `${contribute_img}`,
        linkLabel: 'Je contribue',
        link: '/atelier-des-idees/contribuer',
    },
    {
        title: [
            <h3>
                Détaillez les modalités
                <br />
                du vote
            </h3>,
        ],
        picto: `${vote_picto}`,
        description:
            'Vous trouvez une idée particulièrement “essentielle”, “réalisable” ou “innovante” ? Votez avec l’une ou plusieurs de ces options !',
        img: `${vote_img}`,
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
                    <Steps {...step} />
                ))}
            </div>
        );
    }
}

export default ProposalSteps;
