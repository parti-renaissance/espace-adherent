import React from 'react';
import PropTypes from 'prop-types';
import { Link } from 'react-router-dom';

class FirstForm extends React.Component {
    render() {
        return (
            <div class="first-form">
                <h2>Soumettez votre note ici</h2>
                {/* TODO: link ici and là */}
                <p>
					Une fois ce dernier formulaire rempli, votre note pourra être enrichie
					ici par des contributions d’adhérents pendant 3 semaines. Passé ce
					délai, elle sera affichée là et pourra être soumise aux votes des
					adhérents.
                </p>
            </div>
        );
    }
}

export default FirstForm;
