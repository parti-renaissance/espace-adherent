import React from 'react';
import PropTypes from 'prop-types';
import hpIllustrationRapports from './../../img/hp-illustration-rapports.svg';

class Reports extends React.PureComponent {
    render() {
        return (
            <div className="l__wrapper reports">
                <div className="reports__first-section">
                    <h3 className="reports__first-section__title">
						Nous n’avons pas attendu l’Atelier des idées pour vous consulter
                    </h3>
                    <p className="reports__first-section__text">
						Vous êtes déjà 160 000 à avoir répondu à nos consultations.
                    </p>
                    <button
                        className="reports__first-section__button button button--primary"
                        onClick={() => this.props.onReportBtnClicked(this.props.reports)}
                    >
						Je lis les restitutions
                    </button>
                </div>
                <div className="reports__second-section">
                    <img className="hp-illustration-rapports" src={hpIllustrationRapports} />
                </div>
            </div>
        );
    }
}

Reports.defaultProps = {
    reports: [],
};

Reports.propTypes = {
    reports: PropTypes.arrayOf(
        PropTypes.shape({
            url: PropTypes.string,
            name: PropTypes.string,
            size: PropTypes.string,
        })
    ),
    onReportBtnClicked: PropTypes.func.isRequired,
};

export default Reports;
