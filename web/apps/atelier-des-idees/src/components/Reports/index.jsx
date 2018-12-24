import React from 'react';
import PropTypes from 'prop-types';

import { REPORTS_MODAL } from '../../constants/modalTypes';

class Reports extends React.PureComponent {
    constructor(props) {
        super(props);
    }

    render() {
        return (
            <div className="l__wrapper reports">
                <div className="reports__first-section">
                    <h3 className="reports__first-section__title">
						Nous n’avons pas attendu l’Atelier des Idées pour vous consulter
                    </h3>
                    <p className="reports__first-section__text">
						Consultez les rapports de consultations terminées
                    </p>
                    <button
                        className="reports__first-section__button button button--primary"
                        onClick={() =>
                            this.props.showModal(REPORTS_MODAL, {
                                reports: this.props.reports,
                            })
                        }
                    >
						Je lis les rapports
                    </button>
                </div>
                <div className="reports__second-section">
                    {/* TODO: replace by image */}
                    <div className="form" />
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
            file: PropTypes.string,
            fileName: PropTypes.string,
            size: PropTypes.string,
        })
    ),
};

export default Reports;
